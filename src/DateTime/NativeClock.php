<?php

declare(strict_types=1);

namespace Gertvdb\Types\DateTime;

use DateTimeImmutable;
use DateTimeZone;
use Psr\Clock\ClockInterface;

/**
 * Native clock
 */
final readonly class NativeClock implements ClockInterface, IDateTime
{
    public function __construct()
    {
    }

    /**
     * @throws \Exception
     */
    public function now(): DateTimeImmutable
    {
        return $this->toDateTimeImmutable();
    }

    public function toDateTimeValue(): DateTime
    {
        return DateTime::now($this);
    }

    /**
     * @throws \Exception
     */
    public function toDateTimeImmutable(): DateTimeImmutable
    {
        return new DateTimeImmutable('now', new DateTimeZone('UTC'));
    }
}
