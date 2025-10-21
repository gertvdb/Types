<?php

declare(strict_types=1);

namespace Gertvdb\Types\Array\Dictionary;

use Gertvdb\Types\Array\ArrayValue;
use Gertvdb\Types\Array\IArray;
use InvalidArgumentException;
use Traversable;
use function Gertvdb\Types\isOfType;

/**
 * A type-safe dictionary for scalar keys (int|string only).
 *
 * @template TKey of int|string
 * @template TValue
 * @implements IArray<TValue>
 */
final class ScalarDictionary implements IArray
{
    /** @var ArrayValue Internal storage of key-value pairs. */
    private ArrayValue $value;

    /** @var non-empty-string */
    private string $keyType;

    /** @var non-empty-string */
    private string $valueType;

    /**
     * @param array<TKey, TValue> $items
     */
    private function __construct(array $items, string $keyType, string $valueType)
    {
        if ($keyType !== 'int' && $keyType !== 'string') {
            throw new InvalidArgumentException(
                "ScalarDictionary supports only 'int' or 'string' key types, got '{$keyType}'."
            );
        }

        $this->keyType = $keyType;
        $this->valueType = $valueType;

        foreach ($items as $key => $value) {
            if (!isOfType($key, $keyType)) {
                $actual = get_debug_type($key);
                throw new InvalidArgumentException("Invalid key type: expected {$keyType}, got {$actual}");
            }

            if (!isOfType($value, $valueType)) {
                $actual = get_debug_type($value);
                throw new InvalidArgumentException("Invalid value type: expected {$valueType}, got {$actual}");
            }
        }

        $this->value = ArrayValue::fromArray($items);
    }

    /**
     * Create an empty typed dictionary.
     *
     * @param non-empty-string $keyType Either 'int' or 'string'.
     * @param non-empty-string $valueType PHPStan type string for values.
     * @return self<TKey, TValue>
     */
    public static function empty(string $keyType, string $valueType): self
    {
        return new self([], $keyType, $valueType);
    }

    /**
     * Create a typed dictionary from an associative array.
     * Keys and values are validated against the provided types.
     *
     * @template TTK of int|string
     * @template TTV
     * @param array<TTK, TTV> $items
     * @param non-empty-string $keyType Either 'int' or 'string'.
     * @param non-empty-string $valueType PHPStan type string for values.
     * @return self<TTK, TTV>
     */
    public static function fromArray(array $items, string $keyType, string $valueType): self
    {
        return new self($items, $keyType, $valueType);
    }

    /**
     * Returns a new dictionary with the given key set to the given value.
     *
     * @param TKey $key
     * @param TValue $value
     * @return self<TKey, TValue>
     */
    public function add(int|string $key, mixed $value): self
    {
        if (!isOfType($key, $this->keyType)) {
            $actual = get_debug_type($key);
            throw new InvalidArgumentException("Invalid key type: expected {$this->keyType}, got {$actual}");
        }

        if (!isOfType($value, $this->valueType)) {
            $actual = get_debug_type($value);
            throw new InvalidArgumentException("Invalid value type: expected {$this->valueType}, got {$actual}");
        }

        $new = clone $this;
        $data = $this->value->toArray();
        $data[$key] = $value;
        $new->value = ArrayValue::fromArray($data);

        return $new;
    }

    /**
     * Returns a new dictionary without the given key (no-op if key absent).
     *
     * @param TKey $key
     * @return self<TKey, TValue>
     */
    public function remove(int|string $key): self
    {
        if (!isOfType($key, $this->keyType)) {
            $actual = get_debug_type($key);
            throw new InvalidArgumentException("Invalid key type: expected {$this->keyType}, got {$actual}");
        }

        $new = clone $this;
        $data = $this->value->toArray();
        unset($data[$key]);
        $new->value = ArrayValue::fromArray($data);

        return $new;
    }

    /**
     * Get the value for the given key, or throw if the key is absent.
     *
     * @param TKey $key
     * @return TValue
     */
    public function get(int|string $key): mixed
    {
        if (!isOfType($key, $this->keyType)) {
            $actual = get_debug_type($key);
            throw new InvalidArgumentException("Invalid key type: expected {$this->keyType}, got {$actual}");
        }

        $data = $this->value->toArray();

        if (!array_key_exists($key, $data)) {
            throw new InvalidArgumentException("Key not found in dictionary.");
        }

        return $data[$key];
    }

    /**
     * Check whether the dictionary contains the given key.
     *
     * @param TKey $key
     */
    public function has(int|string $key): bool
    {
        if (!isOfType($key, $this->keyType)) {
            $actual = get_debug_type($key);
            throw new InvalidArgumentException("Invalid key type: expected {$this->keyType}, got {$actual}");
        }

        return array_key_exists($key, $this->value->toArray());
    }

    /** @return Traversable<TKey, TValue> */
    public function getIterator(): Traversable
    {
        // Yield key => value pairs as stored.
        yield from $this->value->toArray();
    }

    /**
     * Number of entries in the dictionary.
     */
    public function count(): int
    {
        return $this->value->count();
    }

    /**
     * Returns a native array copy of the dictionary.
     *
     * @return array<TKey, TValue>
     */
    public function toArray(): array
    {
        return $this->value->toArray();
    }

    /**
     * Returns the underlying ArrayValue storage.
     */
    public function toArrayValue(): ArrayValue
    {
        return $this->value;
    }
}
