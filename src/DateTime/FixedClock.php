<?php

declare(strict_types=1);

namespace Gertvdb\Types\DateTime;

use DateTimeImmutable;
use Psr\Clock\ClockInterface;

final readonly class FixedClock implements ClockInterface, IDateTime
{
    private DateTime $now;

    public function __construct(
        DateTime $now
    ){
        $this->now = $now;
    }

    /**
     * @throws \Exception
     */
    public function now(): \DateTimeImmutable
    {
        return $this->toDateTimeImmutable();
    }

    public function toDateTimeValue(): DateTime
    {
        return $this->now;
    }

    public function toDateTimeImmutable(): DateTimeImmutable
    {
        return $this->now->toDateTimeImmutable();
    }
}
