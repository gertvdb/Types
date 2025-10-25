<?php

declare(strict_types=1);

namespace Gertvdb\Types\Array\HashSet;

use Closure;
use Gertvdb\Types\Array\ArrayValue;
use Gertvdb\Types\Array\IArray;
use Gertvdb\Types\Array\IComparable;
use Gertvdb\Types\Array\IHashable;
use Gertvdb\Types\Array\IHashableComparable;
use Gertvdb\Types\Int\IntValue;
use Gertvdb\Types\String\StringValue;
use InvalidArgumentException;
use Traversable;
use function Gertvdb\Types\isOfType;

/**
 * Immutable, typed and sorted HashSet wrapper.
 *
 * This structure maintains a unique set of hashable-comparable values of a
 * single, uniform type. Items are kept in a deterministic order as provided
 * by a comparator. All operations are immutable: any modifying method returns
 * a new instance without changing the original.
 *
 * Internally this class delegates storage and uniqueness to HashSet while
 * preserving the chosen ordering by re-sorting on every write operation.
 *
 * @template T of IHashableComparable
 * @implements IArray<T>
 */
final class SortedHashSet implements IArray
{
    /**
     * Underlying hash set storage.
     * @var HashSet<T>
     */
    private HashSet $value;

    /**
     * Ensure returned arrays use string keys (avoid PHP casting numeric-string keys to int).
     *
     * @param array<string|int, T> $arr
     * @return array<string, T>
     */
    private function stringKeyed(array $arr): array
    {
        $out = [];
        foreach ($arr as $k => $v) {
            $out[(string) $k] = $v;
        }
        return $out;
    }

    /**
     * String representation of the enforced item type.
     * @var non-empty-string
     */
    public readonly string $type;

    /**
     * Comparator used for sorting.
     *
     * @var Closure(IComparable, IComparable): int
     */
    private Closure $comparator;

    private ?HashSet $sorted = null;

    /**
     * Support for 'int' and 'string'
     *
     * Internally we cast them to IHashable.
     *
     * @param mixed $key
     * @return IHashableComparable
     */
    private function normalizeKey(mixed $key): IHashableComparable
    {
        if (get_debug_type($key) === 'int') {
            $key = IntValue::fromInt($key);
        }

        if (get_debug_type($key) === 'string') {
            $key = StringValue::fromString($key);
        }

        return $key;
    }

    private function sort(): void
    {
        $hashset = HashSet::empty($this->type);
        $values = $this->value->toArray();

        // Sort values with the comparator for stable order
        \usort($values, $this->comparator);

        foreach ($values as $value) {
            $hashset = $hashset->add($value);
        }

        $this->sorted = $hashset;
        $this->value = $hashset;
    }

    /**
     * Private constructor. Use empty() or fromArray().
     *
     * @param non-empty-string $type Expected class-string of T
     * @param callable(IComparable, IComparable): int|null $comparator Optional comparator. If null, uses T::compareTo
     */
    private function __construct(string $type, ?callable $comparator = null)
    {
        $this->type = $type;

        // Choose comparator: provided or default to IComparable::compareTo
        $fallback = ($comparator !== null && !$comparator instanceof Closure
            ? $comparator(...)
            : static fn (IComparable $a, IComparable $b): int => $a->compareTo($b)->value);

        $this->comparator = $comparator instanceof Closure
            ? $comparator
            : $fallback;

        $this->value = HashSet::empty($type);
    }

    /**
     * Creates an empty, typed SortedHashSet.
     *
     * @template TT of IHashableComparable
     * @param class-string<TT> $type
     * @return self<TT>
     */
    public static function empty(string $type): self
    {
        return new self($type);
    }

    /**
     * Adds an item and returns a new, sorted instance.
     *
     * @param T $item
     * @return self<T>
     */
    public function add(IHashableComparable|int|string $item): self
    {
        $new = clone $this;
        $values = $this->value->add($item);
        $new->value = $values;
        $new->sorted = null;
        return $new;
    }

    /**
     * Removes an item and returns a new, sorted instance.
     *
     * @param T $item
     * @return self<T>
     */
    public function remove(IHashableComparable|int|string $item): self
    {
        $data = $this->value->remove($item);
        $new = clone $this;
        $new->value = $data;
        // Mark as unsorted to ensure deterministic order is recomputed lazily
        $new->sorted = null;
        return $new;
    }

    /**
     * Checks if the set has an item with the same hash.
     *
     * @param T $item
     */
    public function has(IHashable|int|string $item): bool
    {
        return $this->value->has($item);
    }

    /**
     * Merges with another SortedHashSet of the same type and resorts.
     *
     * @param self<T> $other
     * @return self<T>
     */
    public function merge(self $other): self
    {
        $data = $this->value->merge($other->value);
        $new = clone $this;
        $new->value = $data;
        $new->sorted = null;
        return $new;
    }

    /**
     * @return Traversable<T>
     */
    public function getIterator(): Traversable
    {
        if (!$this->sorted) {
            $this->sort();
        }

        return $this->sorted->getIterator();
    }

    /**
     * Maps each element using the provided callback and returns a new SortedHashSet.
     * The callback must return a value of the same element type T.
     *
     * @param callable(T): T $callback
     * @return self<T>
     */
    public function map(callable $callback): self
    {
        if (!$this->sorted) {
            $this->sort();
        }

        $data = $this->sorted->map($callback);

        $new = clone $this;
        $new->value = $data;
        $new->sorted = $data;
        return $new;
    }

    /**
     * Returns the number of items in the set.
     */
    public function count(): int
    {
        return $this->value->count();
    }

    /**
     * Returns the underlying ArrayValue wrapper of the internal HashSet.
     *
     * Note: this returns ArrayValue of the underlying HashSet values.
     */
    public function toArrayValue(): ArrayValue
    {
        if (!$this->sorted) {
           $this->sort();
        }
        return $this->sorted->toArrayValue();
    }

    /**
     * Returns the set as a native PHP array keyed by element hash.
     *
     * @return array<string, T>
     */
    public function toArray(): array
    {
        if (!$this->sorted) {
            $this->sort();
        }
        return $this->sorted->toArray();
    }

    /**
     * Returns true if the set contains no items.
     */
    public function isEmpty(): bool
    {
        return $this->value->isEmpty();
    }
}
