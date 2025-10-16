<?php

declare(strict_types=1);

namespace Gertvdb\Types\DateTime;

use Gertvdb\Types\Int\BoundedIntValue;
use Gertvdb\Types\Int\IInt;
use Gertvdb\Types\Int\IntRange;
use Gertvdb\Types\Int\IntValue;

final readonly class YearsOfAge implements IInt
{
    private readonly BoundedIntValue $months;

    private function __construct(int $months)
    {
        $this->months = BoundedIntValue::create($months,
            IntRange::create(0, PHP_INT_MAX)
        );
    }

    public static function on(DateTime $dateTime, DayOfBirth $dayOfBirth): YearsOfAge
    {
        if ($dayOfBirth->isAfter($dateTime)) {
            return new self(0);
        }

        $diff = $dayOfBirth->toDateTimeImmutable()->diff($dateTime->toDateTimeImmutable());
        return new self(($diff->y * 12) + $diff->m);
    }

    public static function from(int $years, int $andXMonths = 0): YearsOfAge
    {
        return new self(($years * 12) + $andXMonths);
    }

    public function inYears(): int
    {
        return (int) floor($this->months->toInt() / 12);
    }

    public function inMonths(): int
    {
        return $this->months->toInt();
    }

    public function toIntValue(): IntValue
    {
        return IntValue::fromInt($this->months->toInt());
    }

    public function toInt(): int
    {
        return $this->months->toInt();
    }
}
