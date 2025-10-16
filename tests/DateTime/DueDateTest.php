<?php

declare(strict_types=1);

namespace Gertvdb\Types\DateTime;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;

final class DueDateTest extends TestCase
{
    private function fixedClock(string $isoUtc): ClockInterface
    {
        return new class($isoUtc) implements ClockInterface {
            public function __construct(private string $isoUtc) {}
            public function now(): \DateTimeImmutable { return new \DateTimeImmutable($this->isoUtc); }
        };
    }

    public function testRejectsPastOrPresentDate(): void
    {
        $clock = $this->fixedClock('2025-01-01T00:00:00Z');
        $past = DateTime::from(Timezone::ETC_UTC, 2024, 12, 31, 23, 59, 59);
        $present = DateTime::from(Timezone::ETC_UTC, 2025, 1, 1, 0, 0, 0);

        $this->expectException(InvalidArgumentException::class);
        DueDate::from($past, $clock);
    }

    public function testAcceptsFutureDateAndProxies(): void
    {
        $clock = $this->fixedClock('2025-01-01T00:00:00Z');
        $future = DateTime::from(Timezone::ETC_UTC, 2025, 1, 2, 0, 0, 0);
        $due = DueDate::from($future, $clock);

        $sameDay = DateTime::from(Timezone::ETC_UTC, 2025, 1, 2, 12, 0, 0);
        $before = DateTime::from(Timezone::ETC_UTC, 2025, 1, 1, 23, 0, 0);
        $after = DateTime::from(Timezone::ETC_UTC, 2025, 1, 3, 0, 0, 0);

        $this->assertTrue($due->isSameDay($sameDay));
        $this->assertTrue($due->isAfter($before));
        $this->assertTrue($due->isBefore($after));
    }
}
