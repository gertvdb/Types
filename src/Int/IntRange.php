<?php

declare(strict_types=1);

namespace Gertvdb\Types\Int;

use InvalidArgumentException;
use Random\RandomException;

final readonly class IntRange
{
    private IntValue $min;
    private IntValue $max;

    public function __construct(?int $min, ?int $max)
    {
        $safeMin = $min ? new IntValue($min) : new IntValue(IntValue::MIN);
        $safeMax = $max ? new IntValue($max) : new IntValue(IntValue::MAX);

        if ($safeMin->toInt() > $safeMax->toInt()) {
            throw new InvalidArgumentException(
                "Minimum value ({$safeMin}) cannot be greater than maximum value ({$safeMax})"
            );
        }

        $this->min = $safeMin;
        $this->max = $safeMax;
    }

    public static function create(?int $min, ?int $max): self
    {
        return new self($min, $max);
    }

    /**
     * Useful to parse from user input (url or forms).
     */
    public static function fromString(?string $min, ?string $max): self
    {
        // When no min or no max, let constructor handle default logic to prevent repeating ourselves.
        $safeMin = $min ? IntValue::fromString($min)->toInt() : NULL;
        $safeMax = $max ? IntValue::fromString($max)->toInt() : NULL;
        return new self($safeMin, $safeMax);
    }

    public function min(): IntValue
    {
        return $this->min;
    }

    public function isMin(IntValue $value): bool
    {
        return $this->min()->toInt() === $value->toInt();
    }

    public function max(): IntValue
    {
        return $this->max;
    }

    public function isMax(IntValue $value): bool
    {
        return $this->max()->toInt() === $value->toInt();
    }

    /**
     * Inclusive check: is the value within [min..max]?
     */
    public function contains(IntValue $value): bool
    {
        return $value->toInt() >= $this->min->toInt() &&
            $value->toInt() <= $this->max->toInt();
    }

    /**
     * Force a value into the range.
     */
    public function clamp(IntValue $value): IntValue
    {
        if ($value->toInt() < $this->min->toInt()) {
            return $this->min;
        }

        if ($value->toInt() > $this->max->toInt()) {
            return $this->max;
        }

        return $value;
    }

    /**
     * Number of integers in the range (inclusive)
     */
    public function length(): int
    {
        return $this->max->toInt() - $this->min->toInt() + 1;
    }

    /**
     * Returns a random IntValue within the range (inclusive).
     * @throws RandomException
     */
    public function randomValue(): IntValue
    {
        $min = $this->min->toInt();
        $max = $this->max->toInt();

        // Use random_int for cryptographically secure random integer
        $rand = random_int($min, $max);

        return new IntValue($rand);
    }
}
