<?php

declare(strict_types=1);

namespace Gertvdb\Types\Float;

use OutOfRangeException;

/**
 * Represents and float value that is between 2 other float values.
 */
final readonly class BoundedFloatValue implements IFloat
{
    private FloatValue $value;
    private FloatRange $range;

    private function __construct(float $value, FloatRange $range)
    {
        $safeValue = FloatValue::create($value);
        if (!$range->contains($safeValue)) {
            throw new OutOfRangeException(
                sprintf(
                    "Value %d not in range [%d, %d]",
                    $value,
                    $range->min()->toFloat(),
                    $range->max()->toFloat()
                ),
                0
            );
        }

        $this->value = $safeValue;
        $this->range = $range;
    }

    public static function create(float $value, FloatRange $range): self
    {
        return new self($value, $range);
    }

    public function range(): FloatRange
    {
        return $this->range;
    }

    public function toFloatValue(): FloatValue
    {
        return $this->value;
    }

    public function toFloat(): float
    {
        return $this->value->toFloat();
    }
}
