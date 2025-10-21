<?php

declare(strict_types=1);

namespace Gertvdb\Types\Array\HashSet;

use Gertvdb\Types\Array\ArrayValue;
use Gertvdb\Types\Array\IArray;
use Gertvdb\Types\Sorting\SortOrder;
use InvalidArgumentException;
use Traversable;
use function Gertvdb\Types\isOfType;

/**
 * Immutable, typed hash set for scalar values (int|string).
 *
 * Items are unique by their string representation and the set is immutable.
 *
 * @template T of int|string
 * @implements IArray<T>
 */
final class ScalarHashSet implements IArray
{
    /**
     * Underlying storage; associative array keyed by (string) cast of the value.
     * Iteration yields values T.
     */
    private ArrayValue $value;

    /**
     * String representation of the enforced item type (either 'int' or 'string').
     * @var non-empty-string
     */
    private string $type;

    /**
     * Private constructor. Use empty() or fromArray().
     *
     * @param list<T> $items Initial items; duplicates are removed.
     * @param non-empty-string $type Expected scalar type for all items ('int' or 'string').
     * @throws InvalidArgumentException If any element does not match the provided type.
     */
    private function __construct(array $items, string $type)
    {
        if ($type !== 'int' && $type !== 'string') {
            throw new InvalidArgumentException(
                "ScalarHashSet supports only 'int' or 'string' key types, got '{$type}'."
            );
        }

        $this->type = $type;

        foreach ($items as $i => $item) {
            if (!isOfType($item, $type)) {
                $actual = get_debug_type($item);
                throw new InvalidArgumentException(
                    "Invalid type at index {$i}: expected {$type}, got {$actual}."
                );
            }
        }

        // Use hash as keys to enforce uniqueness
        $hashes = [];
        foreach ($items as $item) {
            $hashes[(string) $item] = $item;
        }

        $this->value = ArrayValue::fromArray($hashes);
    }

    /**
     * Creates an empty, typed HashSet.
     *
     * @param non-empty-string $type Either 'int' or 'string'.
     * @return self
     */
    public static function empty(string $type): self
    {
        return new self([], $type);
    }

    /**
     * Creates a typed HashSet from a list of items.
     *
     * @template TT of int|string
     * @param list<TT> $items
     * @param non-empty-string $type Either 'int' or 'string'.
     * @return self<TT>
     */
    public static function fromArray(array $items, string $type): self
    {
        return new self($items, $type);
    }

    /**
     * Adds an item to the set and returns a new set instance.
     *
     * @param T $item
     * @return self<T>
     */
    /**
     * Adds an item to the set and returns a new set instance.
     *
     * @param T $item
     * @return self<T>
     */
    public function add(int|string $item): self
    {
        if (!isOfType($item, $this->type)) {
            $actual = get_debug_type($item);
            throw new InvalidArgumentException(
                "Invalid type for add: expected {$this->type}, got {$actual}."
            );
        }

        $values = $this->value->toArray();
        $values[(string) $item] = $item;

        return new self(array_values($values), $this->type);
    }

    /**
     * Removes an item from the set (by its string key) and returns a new set.
     *
     * @param T $item
     * @return self<T>
     */
    public function remove(int|string $item): self
    {
        if (!isOfType($item, $this->type)) {
            $actual = get_debug_type($item);
            throw new InvalidArgumentException(
                "Invalid type for remove: expected {$this->type}, got {$actual}."
            );
        }

        $values = $this->value->toArray();
        if ($this->has($item)) {
            unset($values[(string) $item]);
        }

        return new self(array_values($values), $this->type);
    }

    /**
     * Checks if the set contains an item with the same hash.
     *
     * @param T $item
     */
    public function has(int|string $item): bool
    {
        if (!isOfType($item, $this->type)) {
            $actual = get_debug_type($item);
            throw new InvalidArgumentException(
                "Invalid type for has: expected {$this->type}, got {$actual}."
            );
        }

        return $this->value->key_exists((string) $item);
    }

    /**
     * Merges two sets of the same type.
     *
     * @param self<T> $other
     * @return self<T>
     */
    public function merge(self $other): self
    {
        if ($other->type !== $this->type) {
            throw new InvalidArgumentException(
                "Cannot merge HashSet of type {$other->type} into HashSet of type {$this->type}."
            );
        }

        $values = array_merge($this->value->toArray(), $other->value->toArray());

        return new self(array_values($values), $this->type);
    }

    /**
     * @return Traversable<T>
     */
    public function getIterator(): Traversable
    {
        return $this->value->getIterator();
    }

    /**
     * Maps each element using the provided callback and returns a new HashSet.
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
     * Sorts the set items using the provided comparator and returns a new set.
     * The comparator may return an int (-1,0,1) or a SortOrder instance.
     *
     * @param callable(int|string, int|string): (int|SortOrder) $comparator
     * @return self<T>
     */
    public function sort(callable $comparator): self
    {
        $values = array_values($this->value->toArray());
        \usort($values, static function (int|string $a, int|string $b) use ($comparator): int {
            $result = $comparator($a, $b);
            if ($result instanceof SortOrder) {
                return $result->value();
            }
            return (int) $result;
        });

        return new self($values, $this->type);
    }

    /**
     * Returns the number of items in the set.
     */
    public function count(): int
    {
        return $this->value->count();
    }

    /**
     * Returns the underlying ArrayValue wrapper.
     * Note: While the wrapper itself is not generic, it contains the items of this set.
     */
    public function toArrayValue(): ArrayValue
    {
        return $this->value;
    }

    /**
     * Returns the set as a native PHP array keyed by element hash.
     *
     * @return array<string, T>
     */
    public function toArray(): array
    {
        return $this->value->toArray();
    }

    /**
     * Returns true if the set contains no items.
     */
    public function isEmpty(): bool
    {
        return $this->value->isEmpty();
    }
}
