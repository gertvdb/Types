<?php

declare(strict_types=1);

namespace Gertvdb\Types\Array\Queue;

use Gertvdb\Types\Array\ArrayValue;
use Gertvdb\Types\Array\IArray;
use InvalidArgumentException;
use Traversable;
use UnderflowException;
use function Gertvdb\Types\isOfType;

/**
 * Immutable, typed queue (FIFO) collection.
 *
 * A Queue holds values of a single, uniform type (as specified by `$type`).
 * It is immutable: any operation that would modify the queue returns a new
 * instance instead, leaving the original unchanged.
 * The "front" of the queue is the first element (index 0).
 *
 * Type safety is enforced at construction and on enqueue() using the helper
 * function `Gertvdb\Types\isOfType()`.
 *
 * @template T
 * @implements IArray<T>
 */
final class Queue implements IArray
{
    /**
     * Internal value storage. Wraps a list-like array (sequential integer keys)
     * where the element at index 0 is the front of the queue.
     */
    private ArrayValue $value;

    /**
     * String representation of the enforced item type (e.g. "int", "string", class name).
     */
    private string $type;

    /**
     * Private constructor. Use empty() or fromArray().
     *
     * @param list<mixed> $value Initial items for the queue. The array will be reindexed
     *                           to sequential integer keys with index 0 as the front.
     * @param non-empty-string $type The expected type for all items in the queue.
     * @throws InvalidArgumentException If any element does not match the given type.
     */
    private function __construct(array $value, string $type)
    {
        // Ensure sequential keys (list semantics)
        $this->value = ArrayValue::fromArray(array_values($value));

        // Ensure all elements match the type
        $this->type = $type;
        foreach ($this->value->toArray() as $i => $item) {
            if (!isOfType($item, $type)) {
                $actual = get_debug_type($item);
                throw new InvalidArgumentException(
                    "Invalid type at index {$i}: expected {$type}, got {$actual}."
                );
            }
        }
    }

    /**
     * Creates an empty, typed queue.
     *
     * @param non-empty-string $type
     * @return self
     */
    public static function empty(string $type): self
    {
        return new self([], $type);
    }

    /**
     * Enqueues an item at the back of the queue and returns a new queue instance.
     *
     * @param T $item
     * @return self<T>
     * @throws InvalidArgumentException If the item does not match the queue's type.
     */
    public function enqueue(mixed $item): self
    {
        if (!isOfType($item, $this->type)) {
            $actual = get_debug_type($item);
            throw new InvalidArgumentException(
                "Invalid type for enqueue: expected {$this->type}, got {$actual}."
            );
        }

        $items = $this->value->toArray();
        $items[] = $item; // back of the queue
        return new self($items, $this->type);
    }

    /**
     * Removes the front item from the queue and returns a new queue instance.
     * If the queue is already empty, an empty queue of the same type is returned.
     *
     * @return self<T>
     */
    public function dequeue(): self
    {
        if ($this->count() === 0) {
            return new self([], $this->type);
        }

        $items = $this->value->toArray();
        array_shift($items);
        return new self($items, $this->type);
    }

    /**
     * Returns the item at the front of the queue without removing it.
     *
     * @return T
     * @throws UnderflowException If the queue is empty.
     */
    public function peek(): mixed
    {
        if ($this->count() === 0) {
            throw new UnderflowException("Queue is empty.");
        }

        $values = $this->value->toArray();
        return $values[0];
    }

    /**
     * Returns the number of items in the queue.
     */
    public function count(): int
    {
        return $this->value->count();
    }

    /**
     * Returns a new empty queue of the same type.
     *
     * @return self<T>
     */
    public function clear(): self
    {
        return new self([], $this->type);
    }

    /**
     * @return Traversable<T>
     */
    public function getIterator(): Traversable
    {
        return $this->value->getIterator();
    }

    /**
     * Maps each element using the provided callback and returns a new Queue.
     * The callback must return a value of the same element type T; otherwise
     * an InvalidArgumentException is thrown to preserve type safety.
     *
     * @param callable(T): T $callback
     * @return self<T>
     */
    public function map(callable $callback): self
    {
        $new = array_map($callback, $this->value->toArray());

        // Validate the resulting types
        foreach ($new as $i => $item) {
            if (!isOfType($item, $this->type)) {
                $actual = get_debug_type($item);
                throw new InvalidArgumentException(
                    "Invalid type at index {$i} after map: expected {$this->type}, got {$actual}."
                );
            }
        }

        return new self($new, $this->type);
    }

    /**
     * Returns the underlying ArrayValue wrapper.
     * Note: While the wrapper itself is not generic, it contains the items of this queue.
     */
    public function toArrayValue(): ArrayValue
    {
        return $this->value;
    }

    /**
     * Returns the queue as a native PHP array with list semantics.
     * The element at index 0 is the front of the queue.
     *
     * @return list<T>
     */
    public function toArray(): array
    {
        return $this->value->toArray();
    }
}
