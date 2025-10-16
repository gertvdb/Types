<?php

declare(strict_types=1);

namespace Gertvdb\Types\Float;

use InvalidArgumentException;

final readonly class FloatRange
{
    private FloatValue $min;
    private FloatValue $max;

    public function __construct(?float $min, ?float $max)
    {
        $safeMin = $min ? new FloatValue($min) : new FloatValue(FloatValue::MIN);
        $safeMax = $max ? new FloatValue($max) : new FloatValue(FloatValue::MAX);

        if ($safeMin->toFloat() > $safeMax->toFloat()) {
            throw new InvalidArgumentException(
                "Minimum value ({$safeMin->toFloat()}) cannot be greater than maximum value ({$safeMax->toFloat()})"
            );
        }

        $this->min = $safeMin;
        $this->max = $safeMax;
    }

    public static function create(?float $min, ?float $max): self
    {
        return new self($min, $max);
    }

    /**
     * Useful to parse from user input (url or forms).
     */
    public static function fromString(?string $min, ?string $max): self
    {
        // When no min or no max, let constructor handle default logic to prevent repeating ourselves.
        $safeMin = $min ? FloatValue::fromString($min)->toFloat() : NULL;
        $safeMax = $max ? FloatValue::fromString($max)->toFloat() : NULL;
        return new self($safeMin, $safeMax);
    }

    public function min(): FloatValue
    {
        return $this->min;
    }

    public function isMin(FloatValue $value): bool
    {
        return $this->min()->toFloat() === $value->toFloat();
    }

    public function max(): FloatValue
    {
        return $this->max;
    }

    public function isMax(FloatValue $value): bool
    {
        return $this->max()->toFloat() === $value->toFloat();
    }

    /**
     * Inclusive check: is the value within [min..max]?
     */
    public function contains(FloatValue $value): bool
    {
        return $value->toFloat() >= $this->min->toFloat() &&
            $value->toFloat() <= $this->max->toFloat();
    }

    /**
     * Force a value into the range.
     */
    public function clamp(FloatValue $value): FloatValue
    {
        if ($value->toFloat() < $this->min->toFloat()) {
            return $this->min;
        }

        if ($value->toFloat() > $this->max->toFloat()) {
            return $this->max;
        }

        return $value;
    }

    /**
     * Returns a random FloatValue within the range (inclusive).
     */
    public function random(): FloatValue
    {
        $min = $this->min->toFloat();
        $max = $this->max->toFloat();

        $rand = $min + mt_rand() / mt_getrandmax() * ($max - $min);

        return new FloatValue($rand);
    }
}
