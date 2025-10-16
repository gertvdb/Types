<?php

declare(strict_types=1);

namespace Gertvdb\Types\DateTime;

use Gertvdb\Types\Int\IntValue;
use Gertvdb\Types\Int\Round;

readonly class TimeSlot
{

    public Time $start;
    public Time $end;

    private function __construct(
         Time $start,
         Time $end
    )
    {
        if (!$start->isBefore($end)) {
            throw new \InvalidArgumentException(
                'Start time must be before the end time.'
            );
        }

        if (
            ($start->hour->toInt() > $end->hour->toInt()) ||
            ($start->hour->toInt() === 24 || $end->hour->toInt() > 24)
        ) {
            throw new \InvalidArgumentException(
                'The time slot cannot span multiple days.'
            );
        }

        $this->start = $start;
        $this->end = $end;
    }

    public static function fromTime(Time $start, Time $end): self
    {
        return new self($start, $end);
    }

    /** Check if this slot overlaps with another slot */
    public function overlaps(TimeSlot $other): bool
    {
        return !(
            $this->end->isBefore($other->start) ||
            $this->start->isAfter($other->end)
        );
    }

    // Maybe change both to return duration
    public function toNanoseconds(): IntValue
    {
        return IntValue::fromInt($this->end->toNanoseconds() - $this->start->toNanoseconds());
    }

    public function toSeconds(Round $round): IntValue
    {
        $nanoseconds = $this->toNanoseconds();
        return match ($round) {
            Round::HALF_UP, Round::UP => IntValue::fromInt((int) ceil($nanoseconds->toInt() / 1_000_000_000)),
            Round::HALF_DOWN, Round::DOWN => IntValue::fromInt((int) floor($nanoseconds->toInt() / 1_000_000_000)),
            Round::HALF_EVEN => IntValue::fromInt((int) round($nanoseconds->toInt() / 1_000_000_000, 0, PHP_ROUND_HALF_EVEN)),
            Round::HALF_ODD => IntValue::fromInt((int) round($nanoseconds->toInt() / 1_000_000_000, 0, PHP_ROUND_HALF_ODD)),
        };
    }

}
