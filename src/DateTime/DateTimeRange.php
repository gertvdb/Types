<?php

declare(strict_types=1);

namespace Gertvdb\Types\DateTime;

use InvalidArgumentException;

final readonly class DateTimeRange
{
    private DateTime $min;
    private DateTime $max;

    public function __construct(DateTime $min, DateTime $max)
    {
        if ($min->isAfter($max)) {
            throw new InvalidArgumentException(
                "Minimum value ({$min->toString()}) cannot be greater than maximum value ({$max->toString()})"
            );
        }

        $this->min = $min;
        $this->max = $max;
    }

    public function min(): DateTime
    {
        return $this->min;
    }

    public function max(): DateTime
    {
        return $this->max;
    }
}
