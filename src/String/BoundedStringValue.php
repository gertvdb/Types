<?php

declare(strict_types=1);

namespace Gertvdb\Types\String;

use Gertvdb\Types\Int\IntRange;
use Gertvdb\Types\Int\IntValue;
use OutOfRangeException;
use Stringable;

/**
 * Represents and string value that's length is between 2 other int values.
 */
final readonly class BoundedStringValue implements IString
{
    private StringValue $value;
    private IntRange $range;

    private function __construct(string|Stringable $value, IntRange $range)
    {
        $safeValue = StringValue::fromString($value);
        $length = $safeValue->length();

        if (!$range->contains(IntValue::fromInt($length))) {
            throw new OutOfRangeException(
                sprintf(
                    "Length of string %d not in range [%d, %d]",
                    $value,
                    $range->min(),
                    $range->max()
                )
            );
        }

        $this->value = $safeValue;
        $this->range = $range;
    }

    public static function create(string|Stringable $value, IntRange $range): self
    {
        return new self($value, $range);
    }

    public function range(): IntRange
    {
        return $this->range;
    }

    public function toStringValue(): StringValue
    {
        return $this->value->toStringValue();
    }

    public function toString(): string
    {
        return $this->__toString();
    }

    public function __toString(): string
    {
        return $this->value->toString();
    }
}
