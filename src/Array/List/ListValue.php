<?php

declare(strict_types=1);

namespace Gertvdb\Types\Array\List;

use Gertvdb\Types\Array\ArrayValue;
use Gertvdb\Types\Array\IArray;
use InvalidArgumentException;
use Traversable;
use function Gertvdb\Types\isOfType;

/**
 * Class ListValue
 *
 * Represents an immutable, ordered, typed collection of values with contiguous
 * zero-based integer keys (a PHPStan list). All operations are immutable and
 * return a new instance instead of modifying the current one.
 *
 * The list enforces a single, uniform element type provided at construction
 * time via $type (e.g. 'int', 'string', or a class-string for objects).
 *
 * @template T
 * @implements IArray<T>
 */
final readonly class ListValue implements IArray
{
    /**
     * Internal storage of the values.
     *
     * @var ArrayValue
     */
    private ArrayValue $value;

    /**
     * The enforced element type as string (e.g. 'int', 'string', MyClass::class).
     *
     * @var string
     */
    private string $type;

    /**
     * @param list<T> $value Values to initialize the list with. Keys will be reindexed to be sequential.
     * @param string $type  Type of all elements (e.g. 'int', 'string', MyClass::class)
     */
    private function __construct(array $value, string $type)
    {
        $this->type = $type;
        $this->value = ArrayValue::fromArray(array_values($value)); // ensure sequential keys (list)

        $iterator = $this->value->getIterator();
        foreach ($iterator as $i => $item) {
            if (!isOfType($item, $type)) {
                $actual = get_debug_type($item);
                throw new InvalidArgumentException(
                    "Invalid type at index {$i}: expected {$type}, got {$actual}."
                );
            }
        }
    }

    /**
     * Creates an empty typed list.
     *
     * @param string $type Type of all elements (e.g. 'int', 'string', MyClass::class)
     * @return self<T>
     */
    public static function empty(string $type): self
    {
        return new self([], $type);
    }

    /**
     * Create a typed list from a native PHP array.
     * Keys will be reindexed to be sequential (list semantics).
     *
     * @param list<T> $value
     * @param string $type
     * @return self<T>
     */
    public static function fromArray(array $value, string $type): self
    {
        return new self($value, $type);
    }

    /**
     * Add an element to the end of the list and return a new ListValue.
     *
     * @param T $item
     * @return self<T>
     */
    public function add(mixed $item): self
    {
        if (!isOfType($item, $this->type)) {
            $actual = get_debug_type($item);
            throw new InvalidArgumentException(
                "Invalid type for value: expected {$this->type}, got {$actual}."
            );
        }

        $items = $this->value->toArray();
        $items[] = $item;
        return new self($items, $this->type);
    }

    /**
     * Convert to a generic ArrayValue wrapper.
     *
     * @return ArrayValue
     */
    public function toArrayValue(): ArrayValue
    {
        return $this->value;
    }

    /**
     * Convert to a native PHP array with list semantics.
     *
     * @return list<T>
     */
    public function toArray(): array
    {
        return $this->value->toArray();
    }

    /**
     * Returns an iterator over the list’s values.
     *
     * @return Traversable<int, T>
     */
    public function getIterator(): Traversable
    {
        return $this->value->getIterator();
    }

    /**
     * Map each element using the callback; the callback must return the same
     * element type T to satisfy typing, otherwise an exception is thrown.
     *
     * @param callable(T): T $callback
     * @return self<T>
     */
    public function map(callable $callback): self
    {
        $new = array_map($callback, $this->value->toArray());

        // Validate type after mapping
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
     * Keep only elements for which the callback returns true.
     *
     * @param callable(T): bool $callback
     * @return self<T>
     */
    public function filter(callable $callback): self
    {
        return new self(array_values($this->value->filter($callback)->toArray()), $this->type);
    }

    /**
     * Reduce the list to a single value.
     *
     * @param callable(mixed, T): mixed $callback The reducer receives accumulator and current item.
     * @param mixed $initial Initial accumulator value.
     * @return mixed
     */
    public function reduce(callable $callback, mixed $initial = null): mixed
    {
        return $this->value->reduce($callback, $initial);
    }

    /**
     * Find and return the first element that matches the predicate or null.
     *
     * @param callable(T, int): bool $callback Receives (value, index).
     * @return T|null
     */
    public function find(callable $callback): mixed
    {
        return $this->value->find($callback);
    }

    /**
     * Call the callback for each element.
     *
     * @param callable(T, int): void $callback Receives (value, index).
     */
    public function each(callable $callback): void
    {
        $this->value->each($callback);
    }

    /**
     * True if at least one element satisfies the predicate.
     *
     * @param callable(T, int): bool $callback
     */
    public function some(callable $callback): bool
    {
        return $this->value->some($callback);
    }

    /**
     * True only if all elements satisfy the predicate.
     *
     * @param callable(T, int): bool $callback
     */
    public function every(callable $callback): bool
    {
        return $this->value->every($callback);
    }

    /**
     * Check whether a key exists. For lists, keys are 0..n-1.
     */
    public function key_exists(int $key): bool
    {
        return $this->value->key_exists($key);
    }

    /**
     * Checks whether the list contains the given value (strict comparison).
     *
     * @param T $item
     */
    public function contains(mixed $item): bool
    {
        return $this->value->contains($item);
    }

    /**
     * Get all keys (indexes) of the list.
     *
     * @return list<int>
     */
    public function keys(): array
    {
        return $this->value->keys();
    }

    /**
     * Get all values of the list.
     *
     * @return list<T>
     */
    public function values(): array
    {
        return $this->value->values();
    }

    /**
     * Merge with another list/array of the same element type and return a new list.
     *
     * @param ArrayValue|list<T> $other
     * @return self<T>
     */
    public function merge(ArrayValue|array $other): self
    {
        $values = $other instanceof ArrayValue ? $other->toArray() : $other;

        $merged = $this->value->merge($values)->toArray();
        foreach ($merged as $i => $item) {
            if (!isOfType($item, $this->type)) {
                $actual = get_debug_type($item);
                throw new InvalidArgumentException(
                    "Invalid type in merge at index {$i}: expected {$this->type}, got {$actual}."
                );
            }
        }

        return new self($merged, $this->type);
    }

    /**
     * Return a new list with the order of elements reversed.
     *
     * @return self<T>
     */
    public function reverse(): self
    {
        $reversed = $this->value->reverse()->toArray();
        return new self($reversed, $this->type);
    }

    /**
     * Number of elements in the list.
     */
    public function count(): int
    {
        return $this->value->count();
    }

    /**
     * Whether the list contains no elements.
     */
    public function isEmpty(): bool
    {
        return $this->count() === 0;
    }
}
