<?php

declare(strict_types=1);

namespace Gertvdb\Types\DateTime;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;

final class DayOfBirthTest extends TestCase
{
    private function fixedClock(string $isoUtc): ClockInterface
    {
        return new class($isoUtc) implements ClockInterface {
            public function __construct(private string $isoUtc) {}
            public function now(): \DateTimeImmutable { return new \DateTimeImmutable($this->isoUtc); }
        };
    }

    public function testRejectsFutureDate(): void
    {
        $clock = $this->fixedClock('2025-01-01T00:00:00Z');
        $future = DateTime::from(Timezone::ETC_UTC, 2026, 1, 1, 0, 0, 0);
        $this->expectException(InvalidArgumentException::class);
        DayOfBirth::from($future, $clock);
    }

    public function testWhenAddsMonthsAndIsAgeChecks(): void
    {
        $clock = $this->fixedClock('2025-01-01T00:00:00Z');
        $dobDt = DateTime::from(Timezone::ETC_UTC, 2000, 6, 15, 12, 0, 0);
        $dob = DayOfBirth::from($dobDt, $clock);

        $age18 = YearsOfAge::from(18); // 216 months
        $when18 = $dob->when($age18);
        $expected = DateTime::from(Timezone::ETC_UTC, 2018, 6, 15, 12, 0, 0);
        $this->assertSame((string)$expected, (string)$when18);

        // is(age, on): should be true on the birthday and after; false before
        $onBefore = DateTime::from(Timezone::ETC_UTC, 2018, 6, 14, 0, 0, 0);
        $onExact = DateTime::from(Timezone::ETC_UTC, 2018, 6, 15, 0, 0, 0);
        $onAfter = DateTime::from(Timezone::ETC_UTC, 2018, 6, 16, 0, 0, 0);
        $this->assertFalse($dob->is($age18, $onBefore));
        $this->assertTrue($dob->is($age18, $onExact));
        $this->assertTrue($dob->is($age18, $onAfter));
    }

    public function testProxyComparisonMethods(): void
    {
        $clock = $this->fixedClock('2025-01-01T00:00:00Z');
        $dob = DayOfBirth::from(DateTime::from(Timezone::ETC_UTC, 2000, 1, 1, 0, 0, 0), $clock);

        $a = DateTime::from(Timezone::ETC_UTC, 2000, 1, 1, 12, 0, 0);
        $b = DateTime::from(Timezone::ETC_UTC, 1999, 12, 31, 23, 0, 0);
        $c = DateTime::from(Timezone::ETC_UTC, 2000, 1, 2, 0, 0, 0);

        $this->assertTrue($dob->isSameDay($a));
        $this->assertTrue($dob->isAfter($b));
        $this->assertTrue($dob->isBefore($c));
    }
}
