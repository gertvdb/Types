<?php

declare(strict_types=1);

namespace Gertvdb\Types\Array\Dictionary;

use Closure;
use Gertvdb\Types\Array\ArrayValue;
use Gertvdb\Types\Array\IArray;
use Gertvdb\Types\Array\IComparable;
use Gertvdb\Types\Array\IHashable;
use Gertvdb\Types\Array\IHashableComparable;
use Gertvdb\Types\Int\IntValue;
use Gertvdb\Types\String\StringValue;
use Traversable;

/**
 * Immutable, typed dictionary that keeps entries sorted by a comparator.
 *
 * The dictionary is built on top of Dictionary and guarantees a stable order
 * according to the provided comparator (or the default IComparable::compareTo when applicable).
 *
 * Note: Sorting is applied to the values provided at construction or insertion time
 * to produce a deterministic iteration order. The underlying Dictionary remains type-safe.
 *
 * @template TKey of IHashable
 * @template TValue
 * @implements IArray<TValue>
 */
final class SortedDictionary implements IArray
{
    /**
     * Backing storage as a regular Dictionary.
     *
     * @var Dictionary<TKey, TValue>
     */
    private Dictionary $value;

    /**
     * Comparator used to order values during construction and updates.
     *
     * @var Closure(TValue, TValue): int
     */
    private Closure $comparator;

    private ?Dictionary $sorted = null;

    /**
     * Support for 'int' and 'string'
     *
     * Internally we cast them to IHashableComparable.
     *
     * @param mixed $key
     * @return IHashable
     */
    private function normalizeKey(mixed $key): IHashable
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
        $dict = Dictionary::empty($this->value->keyType, $this->value->valueType);
        $pairs = array_values($this->value->toArray());

        // Sort by values using the comparator
        \usort($pairs, function (array $a, array $b): int {
            return ($this->comparator)($a['value'], $b['value']);
        });

        foreach ($pairs as $pair) {
            $dict = $dict->add($pair['key'], $pair['value']);
        }

        $this->sorted = $dict;
        $this->value = $dict;
    }

    /**
     * @param class-string<TKey> $keyType
     * @param non-empty-string $valueType
     * @param null|callable(TValue, TValue): int $comparator Comparator returning -1, 0, or 1
     */
    private function __construct(string $keyType, string $valueType, ?callable $comparator = null)
    {

        // Choose comparator: provided or default to IComparable::compareTo
        $fallback = ($comparator !== null && !$comparator instanceof Closure
            ? $comparator(...)
            : static fn (IComparable $a, IComparable $b): int => $a->compareTo($b)->value);

        $this->comparator = $comparator instanceof Closure
            ? $comparator
            : $fallback;

        $dictionary = Dictionary::empty($keyType, $valueType);
        $this->value = $dictionary;
    }

    /**
     * Create an empty sorted dictionary with the provided types and optional comparator.
     *
     * @template KK of IHashable
     * @template VV
     * @param class-string<KK> $keyType
     * @param non-empty-string $valueType
     * @param null|callable(VV, VV): int $comparator
     * @return self<KK, VV>
     */
    public static function empty(string $keyType, string $valueType, ?callable $comparator = null): self
    {
        return new self($keyType, $valueType, $comparator);
    }

    /**
     * Add or replace a value for the given key, returning a new sorted dictionary.
     *
     * @param TKey $key
     * @param TValue $value
     * @return self<TKey, TValue>
     */
    public function add(IHashableComparable|int|string $key, mixed $value): self
    {
        $normalizedKey = $this->normalizeKey($key);

        $new = clone $this;
        $values = $this->value->add($normalizedKey, $value);
        $new->value = $values;
        $new->sorted = null;
        return $new;
    }

    /**
     * Remove a key, returning a new sorted dictionary.
     *
     * @param TKey $key
     * @return self<TKey, TValue>
     */
    public function remove(IHashableComparable|int|string $key): self
    {
        $normalizedKey = $this->normalizeKey($key);

        $new = clone $this;
        $values = $this->value->remove($normalizedKey);
        $new->value = $values;
        $new->sorted = null;
        return $new;
    }

    /**
     * Get the value for a given key.
     *
     * @param TKey $key
     * @return TValue
     */
    public function get(IHashableComparable|int|string $key): mixed
    {
        $normalizedKey = $this->normalizeKey($key);
        return $this->value->get($normalizedKey);
    }

    /**
     * Iterate in the sorted order.
     *
     * @return Traversable<TKey, TValue>
     */
    public function getIterator(): Traversable
    {
        if ($this->sorted === null) {
            $this->sort();
        }
        return $this->value->getIterator();
    }

    /**
     * Number of entries.
     */
    public function count(): int
    {
        return $this->value->count();
    }

    /**
     * Underlying ArrayValue of the backing dictionary.
     *
     * @return ArrayValue<array<string, array{key: TKey, value: TValue}>>
     */
    public function toArrayValue(): ArrayValue
    {
        if ($this->sorted === null) {
            $this->sort();
        }
        // Transform underlying pair-structure into values-only array keyed by hash
        $pairs = $this->sorted->toArray();
        $valuesOnly = [];
        foreach ($pairs as $hash => $pair) {
            $valuesOnly[(string)$hash] = $pair['value'];
        }
        return ArrayValue::fromArray($valuesOnly);
    }

    /**
     * Returns a native array as produced by the backing dictionary.
     *
     * @return array<string, TValue>
     */
    public function toArray(): array
    {
        if ($this->sorted === null) {
            $this->sort();
        }
        $pairs = $this->sorted->toArray();
        $valuesOnly = [];
        foreach ($pairs as $hash => $pair) {
            $valuesOnly[(string)$hash] = $pair['value'];
        }
        return $valuesOnly;
    }

    /**
     * Whether no entries exist.
     */
    public function isEmpty(): bool
    {
        return $this->value->isEmpty();
    }
}
