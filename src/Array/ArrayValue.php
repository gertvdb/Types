<?php

declare(strict_types=1);

namespace Gertvdb\Types\Array;

use Gertvdb\Types\Sorting\SortOrder;
use Traversable;

/**
 * Class ArrayValue
 *
 * Represents a native PHP array wrapped in an immutable value object.
 *
 * This class exposes array-like operations (map, filter, reduce, etc.)
 * while preserving immutability — all methods that modify data
 * return a new instance instead of altering the original.
 *
 * Keys and values are preserved as-is, meaning associative, sparse,
 * and mixed arrays are all supported, just like native PHP arrays.
 *
 * @implements IArray<mixed>
 * */
final readonly class ArrayValue implements IArray
{
    /**
     * @var array<mixed> The internal array value.
     */
    private array $value;

    /**
     * @param array<mixed> $value
     */
    private function __construct(array $value)
    {
        $this->value = $value;
    }


    /**
     * Creates an empty ArrayValue.
     */
    public static function empty(): self
    {
        return new self([]);
    }


    /**
     * Creates a new ArrayValue from a native PHP array.
     *
     * @param array<mixed> $value
     */
    public static function fromArray(array $value): self
    {
        return new self($value);
    }


    /**
     * Returns the current instance.
     */
    public function toArrayValue(): ArrayValue
    {
        return $this;
    }


    /**
     * Converts the value object to a native PHP array.
     *
     * @return array<mixed>
     */
    public function toArray(): array
    {
        return $this->value;
    }


    /**
     * Returns the number of elements in the array.
     */
    public function count(): int
    {
        return count($this->value);
    }


    /**
     * Returns true if the array has no elements.
     */
    public function isEmpty(): bool
    {
        return $this->count() === 0;
    }


    /**
     * Returns an iterator over the array’s values.
     *
     * @return Traversable<mixed>
     */
    public function getIterator(): Traversable
    {
        foreach ($this->value as $value) {
            yield $value;
        }
    }


    /**
     * Applies a callback to each element and returns a new ArrayValue
     * containing the transformed results.
     *
     * @param callable(mixed): mixed $callback
     */
    public function map(callable $callback): self
    {
        return new self(array_map($callback, $this->value));
    }


    /**
     * Filters the array using a callback and returns a new ArrayValue
     * containing only elements for which the callback returns true.
     *
     * @param callable(mixed): bool $callback
     */
    public function filter(callable $callback): self
    {
        return new self(array_values(array_filter($this->value, $callback)));
    }


    /**
     * Reduces the array to a single value using a callback.
     *
     * @param callable(mixed, mixed): mixed $callback
     * @param mixed $initial Initial accumulator value.
     */
    public function reduce(callable $callback, mixed $initial = null): mixed
    {
        return array_reduce($this->value, $callback, $initial);
    }


    /**
     * Finds and returns the first element that matches the callback condition.
     * Returns null if no match is found.
     *
     * @param callable(mixed, string|int): bool $callback
     */
    public function find(callable $callback): mixed
    {
        foreach ($this->value as $key => $item) {
            if ($callback($item, $key)) {
                return $item;
            }
        }
        return null;
    }


    /**
     * Calls a callback for each element in the array.
     *
     * @param callable(mixed, string|int): void $callback
     */
    public function each(callable $callback): void
    {
        foreach ($this->value as $key => $item) {
            $callback($item, $key);
        }
    }


    /**
     * Returns true if at least one element satisfies the callback condition.
     *
     * @param callable(mixed, string|int): bool $callback
     */
    public function some(callable $callback): bool
    {
        foreach ($this->value as $key => $item) {
            if ($callback($item, $key)) {
                return true;
            }
        }
        return false;
    }


    /**
     * Returns true only if all elements satisfy the callback condition.
     *
     * @param callable(mixed, string|int): bool $callback
     */
    public function every(callable $callback): bool
    {
        foreach ($this->value as $key => $item) {
            if (!$callback($item, $key)) {
                return false;
            }
        }
        return true;
    }


    /**
     * Checks if a key exists in the array.
     */
    public function key_exists(int|string $key): bool
    {
        return array_key_exists($key, $this->value);
    }


    /**
     * Returns the first key in the array, or null if empty.
     */
    public function key_first(): string|int|null
    {
        return array_key_first($this->value);
    }


    /**
     * Returns the last key in the array, or null if empty.
     */
    public function array_last(): string|int|null
    {
        return array_key_last($this->value);
    }


    /**
     * Returns true if the array contains the given item.
     */
    public function contains(mixed $item): bool
    {
        return in_array($item, $this->value, true);
    }


    /**
     * Returns all keys of the array as a new indexed list.
     *
     * @return array<int, string|int>
     */
    public function keys(): array
    {
        return array_keys($this->value);
    }


    /**
     * Returns all values of the array as a new indexed list.
     *
     * @return array<int, mixed>
     */
    public function values(): array
    {
        return array_values($this->value);
    }


    /**
     * Merges this array with another array or ArrayValue.
     *
     * @param ArrayValue|array<mixed> $other
     */
    public function merge(ArrayValue|array $other): self
    {
        $values = $other instanceof self ? $other->toArray() : $other;
        return new self(array_merge($this->value, $values));
    }


    /**
     * Returns a new ArrayValue with the order of elements reversed.
     */
    public function reverse(): self
    {
        return new self(array_reverse($this->value));
    }

    /**
     * Sorts the array using the provided comparator while preserving keys.
     *
     * The comparator should return either an int (-1, 0, 1) like a standard PHP comparator,
     * or an instance of SortOrder. The method is immutable and returns a new instance.
     *
     * @param callable(mixed, mixed): (int|SortOrder) $comparator
     */
    public function sort(callable $comparator): self
    {
        $arr = $this->value;
        \uasort($arr, static function ($a, $b) use ($comparator): int {
            $result = $comparator($a, $b);
            if ($result instanceof SortOrder) {
                return $result->value();
            }
            return (int) $result;
        });

        return new self($arr);
    }
}
