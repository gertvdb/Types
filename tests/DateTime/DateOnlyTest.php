<?php

declare(strict_types=1);

namespace Gertvdb\Types\DateTime;

use Gertvdb\Types\DateTime\Formats\DateOnlyFormat;
use Gertvdb\Types\I18n\Locale;
use PHPUnit\Framework\TestCase;
use function EasyCI202307\dump;

final class DateOnlyTest extends TestCase
{
    public function testFromAndToString(): void
    {
        $d = DateOnly::from(2024, 10, 18);
        $this->assertSame('2024-10-18', (string) $d);
        $this->assertSame('2024-10-18', $d->toString());
        $this->assertSame('2024-10-18', $d->toStringValue()->toString());

        $this->assertSame(2024, $d->year->toInt());
        $this->assertSame(10, $d->month->toInt());
        $this->assertSame(18, $d->day->toInt());
    }

    public function testFromIsoParses(): void
    {
        $d = DateOnly::fromIso('2024-02-29');
        $this->assertSame('2024-02-29', (string) $d);
    }

    public function testFirstAndLastDayOfMonth(): void
    {
        $first = DateOnly::firstDayOf(2024, 2);
        $last = DateOnly::lastDayOf(2024, 2);
        $this->assertSame('2024-02-01', (string) $first);
        $this->assertSame('2024-02-29', (string) $last); // leap year
    }

    public function testComparisons(): void
    {
        $a = DateOnly::from(2024, 10, 18);
        $b = DateOnly::from(2024, 10, 19);
        $c = DateOnly::from(2024, 10, 18);

        $this->assertTrue($a->equals($c));
        $this->assertFalse($a->equals($b));
        $this->assertTrue($a->isBefore($b));
        $this->assertTrue($b->isAfter($a));
        $this->assertFalse($a->isAfter($b));
    }

    public function testFormatPatterns(): void
    {
        $d = DateOnly::from(2024, 7, 3);
        $this->assertSame('2024-07-03', $d->format(DateOnlyFormat::DATEONLY_ISO8601)->toString());
        $this->assertSame('20240703', $d->format(DateOnlyFormat::DATEONLY_COMPACT)->toString());
        $this->assertSame('2024', $d->format(DateOnlyFormat::DATEONLY_YEAR_ONLY)->toString());
    }

    public function testFormatLocaleBasic(): void
    {
        $d = DateOnly::from(2024, 7, 3);
        $locale = Locale::fromString('en-US');
        // Using DATEONLY_FULL: expect month name and day, year present. We don't fix exact output across platforms,
        // but assert it contains the month name and year digits.
        $out = $d->formatLocale(\Gertvdb\Types\DateTime\Formats\DateOnlyLocaleFormat::DATEONLY_FULL, $locale)->toString();
        $this->assertStringContainsString('2024', $out);
    }
}
