<?php

declare(strict_types=1);

namespace Gertvdb\Types\Array\List;

use Closure;
use Gertvdb\Types\Array\ArrayValue;
use Gertvdb\Types\Array\IArray;
use Gertvdb\Types\Array\IComparable;
use Traversable;

/**
 * SortedList
 *
 * An immutable, typed, and always-sorted list of comparable values. The list
 * keeps contiguous zero-based integer keys (a PHPStan list) and enforces a
 * single, uniform element type $type that must implement IComparable.
 *
 * All mutating operations return a new SortedList instance; the original
 * instance is never modified.
 *
 * @template T of IComparable
 * @implements IArray<T>
 */
final class SortedList implements IArray
{
    /**
     * Internal storage of the values (kept as a typed ListValue).
     *
     * @var ListValue<T>
     */
    private ListValue $value;

    /**
     * The enforced element type as string (e.g. MyClass::class). Must be a
     * class-string of a type that implements IComparable.
     *
     * @var class-string<T>
     */
    public readonly string $type;

    /**
     * Comparator used for sorting.
     *
     * @var Closure(T, T): int
     */
    private Closure $comparator;

    /**
     * @param class-string<T> $type
     * @param null|callable(T,T):int $comparator Optional comparator returning <0, 0, >0
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

        $this->value = ListValue::empty($type);
    }

    /**
     * Create an empty SortedList of a given comparable type.
     *
     * @param class-string<T> $type
     * @param null|callable(T,T):int $comparator
     * @return self<T>
     */
    public static function empty(string $type, ?callable $comparator = null): self
    {
        return new self($type, $comparator);
    }

    /**
     * Add a single item and return a new SortedList with the element placed at
     * the correct sorted position.
     *
     * @param T $item
     * @return self<T>
     */
    public function add(IComparable $item): self
    {
        $new = clone $this;

        $data = $this->value->add($item);
        $new->value = $data;
        return $new;
    }

    /**
     * Convert to a generic ArrayValue wrapper.
     *
     * @return ArrayValue
     */
    public function toArrayValue(): ArrayValue
    {
        return $this->value->toArrayValue();
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

    /**
     * Merge with another array of items or an ArrayValue and return a new
     * SortedList with all items re-sorted.
     *
     * @param ArrayValue|list<T> $other
     * @return self<T>
     */
    public function merge(ArrayValue|array $other): self
    {
        $new = clone $this;
        $data = $this->value->merge($other);
        $new->value = $data;
        return $new;
    }

    /**
     * Map over the items and return a new SortedList. Note: The callback MUST
     * return the same type T for each element; otherwise, an exception will be
     * thrown by the underlying typed ListValue on construction.
     * The resulting list will be re-sorted using the configured comparator.
     *
     * @param callable(T):T $callback
     * @return self<T>
     */
    public function map(callable $callback): self
    {
        $new = clone $this;
        $data = $this->value->map($callback);
        $new->value = $data;
        return $new;
    }
}
