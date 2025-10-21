<?php

declare(strict_types=1);

namespace Gertvdb\Types\Array\HashSet;

use Gertvdb\Types\Array\ArrayValue;
use Gertvdb\Types\Array\IArray;
use Gertvdb\Types\Array\IHashable;
use Gertvdb\Types\Sorting\SortOrder;
use InvalidArgumentException;
use Traversable;
use function Gertvdb\Types\isOfType;

/**
 * Immutable, typed hash set collection.
 *
 * A HashSet holds values of a single, uniform type (as specified by `$type`).
 * All elements must implement IHashable and be of the same type.
 * It is immutable: any operation that would modify the set returns a new
 * instance instead, leaving the original unchanged.
 *
 * Internally, element hashes (IHashable::toHash()) are used as array keys to
 * enforce uniqueness. Iteration yields the values, not the hash keys.
 *
 * @template T of IHashable
 * @implements IArray<T>
 */
final class HashSet implements IArray
{
    /**
     * Underlying storage; associative array using element hash as key.
     * Iteration yields values T.
     */
    private ArrayValue $value;

    /**
     * String representation of the enforced item type (e.g. class name implementing IHashable).
     * @var non-empty-string
     */
    private string $type;

    /**
     * Private constructor. Use empty() or fromArray().
     *
     * @param list<T> $items Initial items; duplicates (by hash) are removed.
     * @param non-empty-string $type Expected type for all items (must implement IHashable).
     * @throws InvalidArgumentException If any element does not implement IHashable or match the type.
     */
    private function __construct(array $items, string $type)
    {
        $this->type = $type;

        foreach ($items as $i => $item) {
            if (!isOfType($item, IHashable::class)) {
                throw new InvalidArgumentException(
                    "Item at index {$i} must implement IHashable."
                );
            }
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
            $hashes[$item->toHash()] = $item;
        }

        $this->value = ArrayValue::fromArray($hashes);
    }

    /**
     * Creates an empty, typed HashSet.
     *
     * @template TT of IHashable
     * @param class-string<TT> $type
     * @return self<TT>
     */
    public static function empty(string $type): self
    {
        return new self([], $type);
    }

    /**
     * Creates a typed HashSet from a list of items.
     *
     * @template TT of IHashable
     * @param list<TT> $items
     * @param non-empty-string $type The expected type for all items.
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
    public function add(IHashable $item): self
    {
        if (!isOfType($item, $this->type)) {
            $actual = get_debug_type($item);
            throw new InvalidArgumentException(
                "Invalid type for add: expected {$this->type}, got {$actual}."
            );
        }

        $values = $this->value->toArray();
        $values[$item->toHash()] = $item;

        return new self(array_values($values), $this->type);
    }

    /**
     * Removes an item from the set (by its hash) and returns a new set.
     *
     * @param T $item
     * @return self<T>
     */
    public function remove(IHashable $item): self
    {
        if (!isOfType($item, $this->type)) {
            $actual = get_debug_type($item);
            throw new InvalidArgumentException(
                "Invalid type for remove: expected {$this->type}, got {$actual}."
            );
        }

        $values = $this->value->toArray();
        if ($this->has($item)) {
            unset($values[$item->toHash()]);
        }

        return new self(array_values($values), $this->type);
    }

    /**
     * Checks if the set contains an item with the same hash.
     *
     * @param T $item
     */
    public function has(IHashable $item): bool
    {
        return $this->value->key_exists($item->toHash());
    }

    /**
     * Sorts the set items using the provided comparator and returns a new set.
     * The comparator may return an int (-1,0,1) or a SortOrder instance.
     *
     * @param callable(IHashable, IHashable): (int|SortOrder) $comparator
     * @return self<T>
     */
    public function sort(callable $comparator): self
    {
        $values = array_values($this->value->toArray());
        \usort($values, static function (IHashable $a, IHashable $b) use ($comparator): int {
            $result = $comparator($a, $b);
            if ($result instanceof SortOrder) {
                return $result->value();
            }
            return (int) $result;
        });

        return new self($values, $this->type);
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
