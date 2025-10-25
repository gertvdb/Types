<?php

declare(strict_types=1);

namespace Gertvdb\Types\Array\Dictionary;

use Gertvdb\Types\Array\ArrayValue;
use Gertvdb\Types\Array\IArray;
use Gertvdb\Types\Array\IHashable;
use Gertvdb\Types\Int\IntValue;
use Gertvdb\Types\String\StringValue;
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
 * @template TKey of IHashable
 * @template TValue of mixed
 * @implements IArray<TValue>
 */
final class Dictionary implements IArray
{
    /**
     * Underlying storage keyed by key hash.
     *
     * @var ArrayValue<array<string, array{key: TKey, value: TValue}>>
     */
    private ArrayValue $value;

    /** @var non-empty-string */
    public readonly string $keyType;

    /** @var non-empty-string */
    public readonly string $valueType;

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

    private function normalizeKeyType(string $keyType): string
    {
        if ($keyType === 'int') {
            $keyType = IntValue::class;
        }

        if ($keyType === 'string') {
            $keyType = StringValue::class;
        }

        return $keyType;
    }

    /**
     * Private constructor.
     *
     * @param non-empty-string $keyType Expected key type (must implement IHashable)
     * @param non-empty-string $valueType Expected value type
     */
    public function __construct(string $keyType, string $valueType)
    {
        $this->keyType = $keyType;
        $this->valueType = $valueType;
        $this->value = ArrayValue::fromArray([]);
    }

    /**
     * Creates an empty, typed dictionary.
     *
     * @param non-empty-string $keyType Fully-qualified class-string of the key type.
     * @param non-empty-string $valueType PHPStan type string of the value type.
     * @return self<TKey, TValue>
     */
    public static function empty(string $keyType, string $valueType): self
    {
        return new self($keyType, $valueType);
    }

    /**
     * Adds or replaces a value for the given key and returns a new dictionary.
     *
     * @param IHashable|int|string $key
     * @param TValue $value
     * @return self<TKey, TValue>
     */
    public function add(IHashable|int|string $key, mixed $value): self
    {
        $normalizedKeyType = $this->normalizeKeyType($this->keyType);
        $normalizedKey = $this->normalizeKey($key);

        if (!isOfType($value, $this->valueType)) {
            $actual = get_debug_type($value);
            throw new InvalidArgumentException("Invalid value type: expected {$this->valueType}, got {$actual}");
        }

        if (!isOfType($normalizedKey, $normalizedKeyType)) {
            $actual = get_debug_type($normalizedKey);
            throw new InvalidArgumentException("Invalid key type: expected {$normalizedKeyType}, got {$actual}");
        }

        if (!($key instanceof IHashable)) {
            throw new InvalidArgumentException("Key must implement " . IHashable::class);
        }

        $new = clone $this;
        $data = $this->value->toArray();
        $data[$normalizedKey->toHash()] = ['key' => $key, 'value' => $value];

        $new->value = ArrayValue::fromArray($data);
        return $new;
    }

    /**
     * Removes a key from the dictionary and returns a new dictionary.
     * If the key does not exist, this is a no-op.
     *
     * @param IHashable|int|string $key
     * @return self<TKey, TValue>
     */
    public function remove(IHashable|int|string $key): self
    {
        $new = clone $this;
        $data = $this->value->toArray();

        $normalizedKey = $this->normalizeKey($key);

        unset($data[$normalizedKey->toHash()]);
        $new->value = ArrayValue::fromArray($data);

        return $new;
    }

    /**
     * Returns the value for a given key.
     *
     * @param TKey $key
     * @return TValue
     * @throws InvalidArgumentException If the key is not present.
     */
    public function get(IHashable|int|string $key): mixed
    {
        $data = $this->value->toArray();

        $normalizedKey = $this->normalizeKey($key);
        $hash = $normalizedKey->toHash();

        if (!isset($data[$hash])) {
            throw new InvalidArgumentException("Key not found in dictionary.");
        }

        return $data[$hash]['value'];
    }

    /**
     * Checks if a key exists in the dictionary.
     *
     * @param TKey $key
     * @return bool
     */
    public function has(IHashable|int|string $key): bool
    {
        $normalizedKey = $this->normalizeKey($key);
        return isset($this->value->toArray()[$normalizedKey->toHash()]);
    }

    /**
     * Iterator over the dictionary yielding original keys and their values.
     *
     * @return Traversable<TKey, TValue>
     */
    public function getIterator(): Traversable
    {
        foreach ($this->value->toArray() as $pair) {
            yield $pair['key'] => $pair['value'];
        }
    }

    /**
     * Number of entries in the dictionary.
     */
    public function count(): int
    {
        return $this->value->count();
    }

    /**
     * Returns a native array indexed by the key hashes with raw values.
     *
     * Note: Keys are not preserved here; iteration preserves original keys.
     *
     * @return array<string, array{key: TKey, value: TValue}>
     */
    public function toArray(): array
    {
        return $this->value->toArray();
    }

    /**
     * Returns the underlying ArrayValue storage.
     *
     * @return ArrayValue<array<string, array{key: TKey, value: TValue}>>
     */
    public function toArrayValue(): ArrayValue
    {
        return $this->value;
    }

    /**
     * The expected key type of this dictionary.
     *
     * @return non-empty-string
     */
    public function keyType(): string
    {
        return $this->keyType;
    }

    /**
     * The expected value type of this dictionary.
     *
     * @return non-empty-string
     */
    public function valueType(): string
    {
        return $this->valueType;
    }

    /**
     * Whether the dictionary contains no entries.
     */
    public function isEmpty(): bool
    {
        return $this->value->isEmpty();
    }
}
