<?php

declare(strict_types=1);

namespace Gertvdb\Types\DateTime;

use InvalidArgumentException;

final readonly class BoundedDateTime
{
    public DateTime $value;
    public DateTimeRange $range;

    private function __construct(DateTime $value, DateTimeRange $range)
    {
        if ($value->isBefore($range->min())) {
            throw new InvalidArgumentException(
                "The datetime ({$value->toString()}) cannot fall before ({$range->min()->toString()})"
            );
        }

        if ($value->isAfter($range->max())) {
            throw new InvalidArgumentException(
                "The datetime ({$value->toString()}) cannot fall after ({$range->max()->toString()})"
            );
        }


        $this->value = $value;
        $this->range = $range;
    }

    public static function create(DateTime $value, DateTimeRange $range): self
    {
        return new self($value, $range);
    }
}
