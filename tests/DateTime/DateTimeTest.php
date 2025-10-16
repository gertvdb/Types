<?php

declare(strict_types=1);

namespace Gertvdb\Types\DateTime;

use Gertvdb\Types\DateTime\Formats\DateTimeFormat;
use Gertvdb\Types\DateTime\Formats\DateTimeLocaleFormat;
use Gertvdb\Types\I18n\Locale;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;

final class DateTimeTest extends TestCase
{
    public function testFromAndToStringAndComponents(): void
    {
        $dt = DateTime::from(Timezone::ETC_UTC, 2024, 10, 18, 15, 42, 30);

        $this->assertStringContainsString('Z', (string) $dt); // ISO string is UTC with Z
        $this->assertSame((string) $dt, $dt->toString());
        $this->assertSame((string) $dt, $dt->toStringValue()->toString());

        $this->assertSame(2024, $dt->year(Timezone::ETC_UTC)->toInt());
        $this->assertSame(10, $dt->month(Timezone::ETC_UTC)->toInt());
        $this->assertSame(18, $dt->day(Timezone::ETC_UTC)->toInt());

        // Switch timezone shouldn't change instant; components may differ in offset timezones
        $this->assertSame(2024, $dt->year(Timezone::ETC_UTC)->toInt());
    }

    public function testFromIsoAndFromUTC(): void
    {
        $dtUtc = DateTime::fromUTC('2024-10-18T15:42:30Z');
        $this->assertStringContainsString('Z', (string) $dtUtc);

        $dt = DateTime::fromIso('2024-10-18T15:42:30', Timezone::ETC_UTC);
        // formatted ISO8601 with timezone +00:00
        $formatted = $dt->format(DateTimeFormat::DATETIME_ISO8601, Timezone::ETC_UTC)->toString();
        $this->assertStringContainsString('+00:00', $formatted);
        $this->assertStringStartsWith('2024-10-18T15:42:30', $formatted);
    }

    public function testFormatLocale(): void
    {
        $dt = DateTime::fromIso('2024-07-03T09:10:11', Timezone::ETC_UTC);
        $locale = Locale::fromString('en-US');
        $out = $dt->formatLocale(DateTimeLocaleFormat::DATETIME_FULL, $locale, Timezone::ETC_UTC)->toString();
        $this->assertNotSame('', $out);
        $this->assertStringContainsString('2024', $out);
    }

    public function testComparisonsAndSameDay(): void
    {
        $a = DateTime::from(Timezone::ETC_UTC, 2024, 10, 18, 12, 0, 0);
        $b = DateTime::from(Timezone::ETC_UTC, 2024, 10, 18, 13, 0, 0);
        $c = DateTime::from(Timezone::ETC_UTC, 2024, 10, 18, 12, 0, 0);

        $this->assertTrue($a->isBefore($b));
        $this->assertTrue($b->isAfter($a));
        $this->assertFalse($a->isAfter($b));
        $this->assertTrue($a->isSameDay($b));
        $this->assertTrue($a->isSameDay($c));
    }

    public function testStartEndOfDayAndAddSubtract(): void
    {
        $dt = DateTime::from(Timezone::ETC_UTC, 2024, 10, 18, 15, 42, 30);
        $start = $dt->startOfDay();
        $end = $dt->endOfDay();
        $this->assertStringStartsWith('2024-10-18T00:00:00', $start->format(DateTimeFormat::DATETIME_ISO8601, Timezone::ETC_UTC)->toString());
        $this->assertStringStartsWith('2024-10-18T23:59:59', $end->format(DateTimeFormat::DATETIME_ISO8601, Timezone::ETC_UTC)->toString());

        $plus = $dt->add(0, 0, 1, 1, 2, 3);
        $minus = $dt->subtract(0, 0, 1, 1, 2, 3);
        $this->assertNotSame((string)$dt, (string)$plus);
        $this->assertNotSame((string)$dt, (string)$minus);
    }

    public function testIsPastAndFutureUsingClock(): void
    {
        $past = DateTime::from(Timezone::ETC_UTC, 2000, 1, 1, 0, 0, 0);
        $future = DateTime::from(Timezone::ETC_UTC, 2100, 1, 1, 0, 0, 0);

        $clock = new class implements ClockInterface {
            public function now(): \DateTimeImmutable { return new \DateTimeImmutable('2025-01-01T00:00:00Z'); }
        };

        $this->assertTrue($past->isPast($clock));
        $this->assertFalse($past->isFuture($clock));
        $this->assertTrue($future->isFuture($clock));
        $this->assertFalse($future->isPast($clock));
    }

    public function testToFromTimestamp(): void
    {
        $original = DateTime::from(Timezone::ETC_UTC, 2024, 01, 02, 03, 04, 05);
        $ts = $original->timestamp();
        $copy = DateTime::fromTimestamp($ts);
        $this->assertSame((string)$original, (string)$copy);
    }
}
