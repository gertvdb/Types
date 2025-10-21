<?php

declare(strict_types=1);

namespace Gertvdb\Types\Array\Array;

use Gertvdb\Types\Array\ArrayValue;
use Gertvdb\Types\Array\IArray;
use InvalidArgumentException;
use OutOfRangeException;
use Traversable;
use function Gertvdb\Types\isOfType;

/**
 * Class FixedArray
 *
 * (because Array is reserved keyword in PHP)
 *
 * Represents a fixed-size, ordered collection of values.
 * Once created, its length cannot change. All elements must be
 * of the same type. Elements can be accessed and replaced by index.
 */
final readonly class FixedArray implements IArray
{
    /** @var ArrayValue Internal array of values */
    private ArrayValue $value;

    /** @var string The enforced type of all elements */
    private string $type;

    /**
     * Private constructor to enforce immutability of length and type.
     *
     * @param array<int, mixed> $items
     * @param string $type
     */
    private function __construct(array $items, string $type)
    {
        $this->value = ArrayValue::fromArray(array_values($items)); // ensure zero-based keys
        $this->type = $type;

        foreach ($this->value->getIterator() as $i => $item) {
            if (!isOfType($item, $type)) {
                $actual = get_debug_type($item);
                throw new InvalidArgumentException(
                    "Invalid type at index {$i}: expected {$type}, got {$actual}."
                );
            }
        }
    }

    /**
     * Create a FixedArray from a given array and enforce type.
     *
     * @param array<int, mixed> $items
     * @param string $type e.g. 'int', 'string', 'float', 'bool', 'object', MyClass::class
     */
    public static function fromArray(array $items, string $type): self
    {
        return new self($items, $type);
    }

    /**
     * Returns the number of elements.
     */
    public function count(): int
    {
        return $this->value->count();
    }

    /**
     * Returns true if empty.
     */
    public function isEmpty(): bool
    {
        return $this->value->isEmpty();
    }

    /**
     * Get an element by index.
     *
     * @throws OutOfRangeException
     */
    public function get(int $index): mixed
    {
        if ($index < 0 || $index >= $this->count()) {
            throw new OutOfRangeException("Index {$index} is out of bounds.");
        }

        return $this->value->toArray()[$index];
    }

    /**
     * Replace an element at a specific index and return a new FixedArray.
     *
     * @param int $index
     * @param mixed $value
     * @return self
     * @throws OutOfRangeException
     */
    public function set(int $index, mixed $value): self
    {
        if ($index < 0 || $index >= $this->count()) {
            throw new OutOfRangeException("Index {$index} is out of bounds.");
        }

        if (!isOfType($value, $this->type)) {
            $actual = get_debug_type($value);
            throw new InvalidArgumentException(
                "Invalid type for value at index {$index}: expected {$this->type}, got {$actual}."
            );
        }

        $new = $this->value->toArray();
        $new[$index] = $value;
        return new self($new, $this->type);
    }

    /**
     * Convert to native array.
     *
     * @return array<int, mixed>
     */
    public function toArray(): array
    {
        return $this->value->toArray();
    }

    /**
     * Return an iterator over the elements.
     *
     * @return Traversable<int, mixed>
     */
    public function getIterator(): Traversable
    {
        return $this->value->getIterator();
    }

    public function toArrayValue(): ArrayValue
    {
        return $this->value;
    }
}
