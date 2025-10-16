<?php

declare(strict_types=1);

namespace Gertvdb\Types\Int;

use OutOfRangeException;

/**
 * Represents and int value that is between 2 other int values.
 */
final readonly class BoundedIntValue implements IInt
{

    private IntValue $value;
    private IntRange $range;

    private function __construct(int $value, IntRange $range)
    {
        $safeValue = IntValue::fromInt($value);
        if (!$range->contains($safeValue)) {
            throw new OutOfRangeException(
                sprintf(
                    "Value %d not in range [%d, %d]",
                    $value,
                    $range->min(),
                    $range->max()
                )
            );
        }

        $this->value = $safeValue;
        $this->range = $range;
    }

    public static function create(int $value, IntRange $range): self
    {
        return new self($value, $range);
    }

    public function range(): IntRange
    {
        return $this->range;
    }

    public function toIntValue(): IntValue
    {
        return $this->value;
    }

    public function toInt(): int
    {
        return $this->value->toInt();
    }
}
