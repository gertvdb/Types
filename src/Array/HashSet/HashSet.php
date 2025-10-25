<?php

declare(strict_types=1);

namespace Gertvdb\Types\Array\HashSet;

use Gertvdb\Types\Array\ArrayValue;
use Gertvdb\Types\Array\IArray;
use Gertvdb\Types\Array\IHashable;
use Gertvdb\Types\Int\IntValue;
use Gertvdb\Types\String\StringValue;
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
    public readonly string $type;

    /**
     * Support for 'int' and 'string'
     *
     * Internally we cast them to IHashable.
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

    /**
     * Private constructor. Use empty() or fromArray().
     *
     * @param list<T> $items Initial items; duplicates (by hash) are removed.
     * @param non-empty-string $type Expected type for all items (must implement IHashable).
     * @throws InvalidArgumentException If any element does not implement IHashable or match the type.
     */
    private function __construct(string $type)
    {
        $this->type = $type;
        $this->value = ArrayValue::fromArray([]);
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
        return new self($type);
    }

    /**
     * Adds an item to the set and returns a new set instance.
     *
     * @param T $item
     * @return self<T>
     */
    public function add(IHashable|int|string $item): self
    {

        $normalizedKey = $this->normalizeKey($item);

        if (!isOfType($item, $this->type)) {
            $actual = get_debug_type($item);
            throw new InvalidArgumentException(
                "Invalid type for add: expected {$this->type}, got {$actual}."
            );
        }

        if (!isOfType($item, IHashable::class)) {
            throw new InvalidArgumentException(
                "Item at must implement IHashable."
            );
        }


        $new = clone $this;
        $data = $this->value->toArray();
        $data[$normalizedKey->toHash()] = $item;

        $new->value = ArrayValue::fromArray($data);
        return $new;
    }

    /**
     * Removes an item from the set (by its hash) and returns a new set.
     *
     * @param T $item
     * @return self<T>
     */
    public function remove(IHashable|int|string $item): self
    {
        $normalizedKey = $this->normalizeKey($item);

        if (!isOfType($item, $this->type)) {
            $actual = get_debug_type($item);
            throw new InvalidArgumentException(
                "Invalid type for remove: expected {$this->type}, got {$actual}."
            );
        }

        $new = clone $this;
        $data = $this->value->toArray();
        if ($this->has($normalizedKey)) {
            unset($data[$normalizedKey->toHash()]);
        }

        $new->value = ArrayValue::fromArray($data);
        return $new;
    }

    /**
     * Checks if the set contains an item with the same hash.
     *
     * @param T $item
     */
    public function has(IHashable|int|string $item): bool
    {
        $normalizedKey = $this->normalizeKey($item);
        return $this->value->key_exists($normalizedKey->toHash());
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

        $new = clone $this;
        $values = array_merge($this->value->toArray(), $other->value->toArray());
        $new->value = ArrayValue::fromArray($values);
        return $new;
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
        $values = array_map($callback, $this->value->toArray());

        // Validate the resulting types
        foreach ($values as $i => $item) {
            if (!isOfType($item, $this->type)) {
                $actual = get_debug_type($item);
                throw new InvalidArgumentException(
                    "Invalid type at index {$i} after map: expected {$this->type}, got {$actual}."
                );
            }
        }

        $new = clone $this;
        $new->value = ArrayValue::fromArray($values);
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
