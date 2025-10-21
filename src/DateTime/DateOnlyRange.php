<?php


declare(strict_types=1);

namespace Gertvdb\Types\DateTime;

use InvalidArgumentException;

final readonly class DateOnlyRange
{
    private DateOnly $min;
    private DateOnly $max;

    public function __construct(DateOnly $min, DateOnly $max)
    {
        if ($min->isAfter($max)) {
            throw new InvalidArgumentException(
                "Minimum value ({$min->toString()}) cannot be greater than maximum value ({$max->toString()})"
            );
        }

        $this->min = $min;
        $this->max = $max;
    }

    public function min(): DateOnly
    {
        return $this->min;
    }

    public function max(): DateOnly
    {
        return $this->max;
    }
}
