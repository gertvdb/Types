<?php

declare(strict_types=1);

namespace Gertvdb\Types\Array\Stack;

use Gertvdb\Types\Array\ArrayValue;
use Gertvdb\Types\Array\IArray;
use InvalidArgumentException;
use Traversable;
use UnderflowException;
use function Gertvdb\Types\isOfType;

/**
 * Immutable, typed stack (LIFO) collection.
 *
 * A Stack holds values of a single, uniform type (as specified by `$type`).
 * It is immutable: any operation that would modify the stack returns a new
 * instance instead, leaving the original unchanged.
 * The "top" of the stack is the first element (index 0).
 *
 * Type safety is enforced at construction and on push() using the helper
 * function `Gertvdb\Types\isOfType()`.
 *
 * @template T
 * @implements IArray<T>
 */
final class Stack implements IArray
{
    /**
     * Internal value storage. Wraps a list-like array (sequential integer keys)
     * where the element at index 0 is the top of the stack.
     */
    private ArrayValue $value;

    /**
     * String representation of the enforced item type (e.g. "int", "string", class name).
     */
    private string $type;

    /**
     * Private constructor. Use empty() or fromArray().
     *
     * @param list<mixed> $value Initial items for the stack. The array will be reindexed
     *                           to sequential integer keys with index 0 as the top.
     * @param non-empty-string $type The expected type for all items in the stack.
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
     * Creates an empty, typed stack.
     *
     * @param non-empty-string $type
     * @return self
     */
    public static function empty(string $type): self
    {
        return new self([], $type);
    }

    /**
     * Creates a typed stack from a list of items.
     * Items will be reindexed so that the first element becomes the top.
     *
     * @template TT
     * @param list<TT> $items
     * @param non-empty-string $type The expected type for all items.
     * @return self<TT>
     * @throws InvalidArgumentException If any element does not match the given type.
     */
    public static function fromArray(array $items, string $type): self
    {
        return new self($items, $type);
    }

    /**
     * Returns the number of items on the stack.
     */
    public function count(): int
    {
        return $this->value->count();
    }

    /**
     * Pushes an item onto the top of the stack and returns a new stack instance.
     *
     * @param T $item
     * @return self<T>
     * @throws InvalidArgumentException If the item does not match the stack's type.
     */
    public function push(mixed $item): self
    {
        if (!isOfType($item, $this->type)) {
            $actual = get_debug_type($item);
            throw new InvalidArgumentException(
                "Invalid type for push: expected {$this->type}, got {$actual}."
            );
        }

        $items = $this->value->toArray();
        array_unshift($items, $item); // top of stack at start
        return new self($items, $this->type);
    }

    /**
     * Removes the top item from the stack and returns a new stack instance.
     * If the stack is already empty, an empty stack of the same type is returned.
     *
     * @return self<T>
     */
    public function pop(): self
    {
        if ($this->count() === 0) {
            return new self([], $this->type);
        }

        $items = $this->value->toArray();
        array_shift($items);
        return new self($items, $this->type);
    }

    /**
     * Returns the item on the top of the stack without removing it.
     *
     * @return T
     * @throws UnderflowException If the stack is empty.
     */
    public function peek(): mixed
    {
        if ($this->count() === 0) {
            throw new UnderflowException("Stack is empty.");
        }

        $values = $this->value->toArray();
        return $values[0];
    }

    /**
     * @return Traversable<T>
     */
    public function getIterator(): Traversable
    {
        return $this->value->getIterator();
    }

    /**
     * Maps each element using the provided callback and returns a new Stack.
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
     * Note: While the wrapper itself is not generic, it contains the items of this stack.
     */
    public function toArrayValue(): ArrayValue
    {
        return $this->value;
    }

    /**
     * Returns the stack as a native PHP array with list semantics.
     * The element at index 0 is the top of the stack.
     *
     * @return list<T>
     */
    public function toArray(): array
    {
        return $this->value->toArray();
    }
}
