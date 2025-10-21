<?php

declare(strict_types=1);

namespace Gertvdb\Types\DateTime;

use DateTimeImmutable;
use InvalidArgumentException;
use Psr\Clock\ClockInterface;

final readonly class DueDate implements IDateTime
{
    private DateTime $dateTime;

    protected function __construct(
        DateTime $dateTime,
        ClockInterface $clock,
    ) {
        if (!$dateTime->isFuture($clock)) {
            throw new InvalidArgumentException(
                "A due date needs to be in the future"
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
