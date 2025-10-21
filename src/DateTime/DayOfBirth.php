<?php

declare(strict_types=1);

namespace Gertvdb\Types\DateTime;

use DateTimeImmutable;
use InvalidArgumentException;
use Psr\Clock\ClockInterface;

final readonly class DayOfBirth implements IDateTime
{
    private DateTime $dateTime;

    protected function __construct(
        DateTime $dateTime,
        ClockInterface $clock,
    ) {
        if ($dateTime->isFuture($clock)) {
            throw new InvalidArgumentException(
                "A day of birth needs to be in the past"
            );
        }

        $this->dateTime = $dateTime;
    }

    public static function from(
        DateTime $dateTime,
        ClockInterface $clock,
    ): self {
        return new self($dateTime, $clock);
    }

    public function is(YearsOfAge $age, DateTime $on): bool
    {
        $whenAge = $this->when($age);

        $whenStart = $whenAge->startOfDay();
        $onStart = $on->startOfDay();

        return $whenStart->isSameDay($onStart) || $whenStart->isBefore($onStart);
    }

    public function when(YearsOfAge $age): DateTime
    {
        $transformed = $this->dateTime->add(months: $age->inMonths());
        return DateTime::fromTimestamp($transformed->timestamp());
    }

    public function isSameDay(DateTime $dateTime): bool
    {
        return $this->dateTime->isSameDay($dateTime);
    }

    public function isBefore(DateTime $dateTime): bool
    {
        return $this->dateTime->isBefore($dateTime);
    }

    public function isAfter(DateTime $dateTime): bool
    {
        return $this->dateTime->isAfter($dateTime);
    }

    public function toDateTimeValue(): DateTime
    {
        return $this->dateTime->toDateTimeValue();
    }

    public function toDateTimeImmutable(): DateTimeImmutable
    {
        return $this->dateTime->toDateTimeImmutable();
    }
}
