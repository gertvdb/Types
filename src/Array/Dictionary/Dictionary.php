<?php

declare(strict_types=1);

namespace Gertvdb\Types\Array\Dictionary;

use Gertvdb\Types\Array\ArrayValue;
use Gertvdb\Types\Array\IArray;
use Gertvdb\Types\Array\IHashable;
use InvalidArgumentException;
use Traversable;
use function Gertvdb\Types\isOfType;

/**
 * Immutable, typed key-value dictionary.
 *
 * Keys must implement IHashable and match the declared $keyType.
 * Values must match the declared $valueType.
 *
 * Internally, keys are normalized using their hash (IHashable::toHash())
 * to allow efficient lookups. Iteration yields the original key objects
 * as keys and their corresponding values.
 *
 * @template K of IHashable
 * @template V
 * @implements IArray<V>
 */
final class Dictionary implements IArray
{
    /**
     * Underlying storage keyed by key hash.
     *
     * @var ArrayValue<array<string, array{key: K, value: V}>>
     */
    private ArrayValue $value;

    /** @var non-empty-string */
    private string $keyType;

    /** @var non-empty-string */
    private string $valueType;

    /**
     * Private constructor. Use empty() to create a new dictionary.
     *
     * @param array<string, array{key: K, value: V}> $items Normalized storage keyed by key hash.
     * @param non-empty-string $keyType Expected key type (must implement IHashable)
     * @param non-empty-string $valueType Expected value type
     */
    private function __construct(array $items, string $keyType, string $valueType)
    {
        $this->keyType = $keyType;
        $this->valueType = $valueType;

        // Validate keys and values
        foreach ($items as $key => $value) {
            if (!isOfType($key, $keyType)) {
                $actual = get_debug_type($key);
                throw new InvalidArgumentException("Invalid key type: expected {$keyType}, got {$actual}");
            }

            if (!isOfType($value, $valueType)) {
                $actual = get_debug_type($value);
                throw new InvalidArgumentException("Invalid value type: expected {$valueType}, got {$actual}");
            }

            if (!($key instanceof IHashable)) {
                throw new InvalidArgumentException("Key must implement " . IHashable::class);
            }
        }

        // Normalize using key hashes
        $normalized = [];
        foreach ($items as $key => $value) {
            /** @var IHashable $key */
            $normalized[$key->toHash()] = ['key' => $key, 'value' => $value];
        }

        $this->value = ArrayValue::fromArray($normalized);
    }

    /**
     * Creates an empty, typed dictionary.
     *
     * @template KK of IHashable
     * @param class-string<KK> $keyType
     * @param non-empty-string $valueType
     * @return self<KK, mixed>
     */
    public static function empty(string $keyType, string $valueType): self
    {
        return new self([], $keyType, $valueType);
    }

    /**
     * Adds or replaces a value for the given key and returns a new dictionary.
     *
     * @param K $key
     * @param V $value
     * @return self<K, V>
     */
    public function add(IHashable $key, mixed $value): self
    {
        if (!isOfType($value, $this->valueType)) {
            $actual = get_debug_type($value);
            throw new InvalidArgumentException("Invalid value type: expected {$this->valueType}, got {$actual}");
        }

        if (!isOfType($key, $this->keyType)) {
            $actual = get_debug_type($key);
            throw new InvalidArgumentException("Invalid key type: expected {$this->keyType}, got {$actual}");
        }

        $new = clone $this;
        $data = $this->value->toArray();

        $data[$key->toHash()] = ['key' => $key, 'value' => $value];
        $new->value = ArrayValue::fromArray($data);

        return $new;
    }

    /**
     * Removes a key from the dictionary and returns a new dictionary.
     * If the key does not exist, this is a no-op.
     *
     * @param K $key
     * @return self<K, V>
     */
    public function remove(IHashable $key): self
    {
        $new = clone $this;
        $data = $this->value->toArray();

        unset($data[$key->toHash()]);
        $new->value = ArrayValue::fromArray($data);

        return $new;
    }

    /**
     * Returns the value for a given key.
     *
     * @param K $key
     * @return V
     * @throws InvalidArgumentException If the key is not present.
     */
    public function get(IHashable $key): mixed
    {
        $data = $this->value->toArray();
        $hash = $key->toHash();

        if (!isset($data[$hash])) {
            throw new InvalidArgumentException("Key not found in dictionary.");
        }

        return $data[$hash]['value'];
    }

    /**
     * Checks if a key exists in the dictionary.
     *
     * @param K $key
     */
    public function has(IHashable $key): bool
    {
        return isset($this->value->toArray()[$key->toHash()]);
    }

    /**
     * @return Traversable<K, V>
     */
    public function getIterator(): Traversable
    {
        foreach ($this->value->toArray() as $pair) {
            yield $pair['key'] => $pair['value'];
        }
    }

    /**
     * Returns the number of pairs in the dictionary.
     */
    public function count(): int
    {
        return $this->value->count();
    }

    /**
     * Returns the dictionary as a native array keyed by the key hash.
     * The original key objects are available via iteration; toArray() focuses on values.
     *
     * @return array<string, V>
     */
    public function toArray(): array
    {
        $result = [];
        foreach ($this->value->toArray() as $hash => $pair) {
            $result[$hash] = $pair['value'];
        }

        return $result;
    }

    /**
     * Returns the internal ArrayValue wrapper containing normalized storage.
     *
     * @return ArrayValue<array<string, array{key: K, value: V}>>
     */
    public function toArrayValue(): ArrayValue
    {
        return $this->value;
    }
}
