<?php

declare(strict_types=1);

namespace Gertvdb\Types\DateTime;

use InvalidArgumentException;

final readonly class BoundedDateOnly
{
    public DateOnly $value;
    public DateOnlyRange $range;

    private function __construct(DateOnly $value, DateOnlyRange $range)
    {
        if ($value->isBefore($range->min())) {
            throw new InvalidArgumentException(
                "The dateonly ({$value->toString()}) cannot fall before ({$range->min()->toString()})"
            );
        }

        if ($value->isAfter($range->max())) {
            throw new InvalidArgumentException(
                "The dateonly ({$value->toString()}) cannot fall after ({$range->max()->toString()})"
            );
        }

        $this->value = $value;
        $this->range = $range;
    }

    public static function create(DateOnly $value, DateOnlyRange $range): self
    {
        return new self($value, $range);
    }
}
