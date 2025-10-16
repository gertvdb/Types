<?php

declare(strict_types=1);

namespace Gertvdb\Types\DateTime;

use PHPUnit\Framework\TestCase;
use Gertvdb\Types\DateTime\YearAndMonth;
use Gertvdb\Types\DateTime\DateOnly;
use Gertvdb\Types\DateTime\Formats\YearAndMonthFormat;
use Gertvdb\Types\DateTime\Formats\YearAndMonthLocaleFormat;
use Gertvdb\Types\I18n\Locale;

final class YearAndMonthTest extends TestCase
{
    public function testFromIntCreatesYearAndMonth(): void
    {
        $ym = YearAndMonth::fromInt(2024, 3);
        $this->assertSame(2024, $ym->year->toInt());
        $this->assertSame(3, $ym->month->toInt());
    }

    public function testFromStringCreatesYearAndMonth(): void
    {
        $ym = YearAndMonth::fromString('2025', '09');
        $this->assertSame(2025, $ym->year->toInt());
        $this->assertSame(9, $ym->month->toInt());
    }

    public function testDaysInMonthAcrossEdgeCases(): void
    {
        // February non-leap year
        $feb2023 = YearAndMonth::fromInt(2023, 2);
        $this->assertSame(28, $feb2023->daysInMonth()->toInt());

        // February leap year
        $feb2024 = YearAndMonth::fromInt(2024, 2);
        $this->assertSame(29, $feb2024->daysInMonth()->toInt());

        // April has 30 days
        $apr2024 = YearAndMonth::fromInt(2024, 4);
        $this->assertSame(30, $apr2024->daysInMonth()->toInt());

        // January has 31 days
        $jan2024 = YearAndMonth::fromInt(2024, 1);
        $this->assertSame(31, $jan2024->daysInMonth()->toInt());
    }

    public function testFirstAndLastDayOfMonth(): void
    {
        $ym = YearAndMonth::fromInt(2024, 3);
        $first = $ym->firstDayOfMonth();
        $last = $ym->lastDayOfMonth();

        $this->assertSame('2024-03-01', (string) $first);
        $this->assertSame('2024-03-31', (string) $last);

        $febLeap = YearAndMonth::fromInt(2024, 2);
        $this->assertSame('2024-02-29', (string) $febLeap->lastDayOfMonth());
    }

    public function testFormatNumericPatterns(): void
    {
        $ym = YearAndMonth::fromInt(2024, 3);
        $this->assertSame('2024-03', $ym->format(YearAndMonthFormat::ISO)->toString());
        $this->assertSame('03/2024', $ym->format(YearAndMonthFormat::MONTH_YEAR)->toString());
        $this->assertSame('2024/03', $ym->format(YearAndMonthFormat::YEAR_MONTH)->toString());

        $ym2 = YearAndMonth::fromInt(1999, 11);
        $this->assertSame('1999-11', $ym2->format(YearAndMonthFormat::ISO)->toString());
        $this->assertSame('11/1999', $ym2->format(YearAndMonthFormat::MONTH_YEAR)->toString());
        $this->assertSame('1999/11', $ym2->format(YearAndMonthFormat::YEAR_MONTH)->toString());
    }

    public function testFormatLocaleEnglish(): void
    {
        $ym = YearAndMonth::fromInt(2024, 3);
        $en = Locale::fromString('en-US');

        $this->assertSame('March 2024', $ym->formatLocale($en, YearAndMonthLocaleFormat::FULL)->toString());
        $this->assertSame('Mar 2024', $ym->formatLocale($en, YearAndMonthLocaleFormat::MEDIUM)->toString());
        $this->assertSame('2024 March', $ym->formatLocale($en, YearAndMonthLocaleFormat::FULL_REVERSE)->toString());
        $this->assertSame('2024 Mar', $ym->formatLocale($en, YearAndMonthLocaleFormat::MEDIUM_REVERSE)->toString());
    }

    public function testFormatLocaleFrench(): void
    {
        $ym = YearAndMonth::fromInt(2024, 3);
        $fr = Locale::fromString('fr-FR');

        $this->assertSame('mars 2024', $ym->formatLocale($fr, YearAndMonthLocaleFormat::FULL)->toString());
        $this->assertSame('mars 2024', $ym->formatLocale($fr, YearAndMonthLocaleFormat::MEDIUM)->toString());
        $this->assertSame('2024 mars', $ym->formatLocale($fr, YearAndMonthLocaleFormat::FULL_REVERSE)->toString());
        $this->assertSame('2024 mars', $ym->formatLocale($fr, YearAndMonthLocaleFormat::MEDIUM_REVERSE)->toString());
    }

    public function testFormatLocaleDutch(): void
    {
        $ym = YearAndMonth::fromInt(2024, 3);
        $nl = Locale::fromString('nl-NL');

        $this->assertSame('maart 2024', $ym->formatLocale($nl, YearAndMonthLocaleFormat::FULL)->toString());
        $this->assertSame('mrt 2024', $ym->formatLocale($nl, YearAndMonthLocaleFormat::MEDIUM)->toString());
        $this->assertSame('2024 maart', $ym->formatLocale($nl, YearAndMonthLocaleFormat::FULL_REVERSE)->toString());
        $this->assertSame('2024 mrt', $ym->formatLocale($nl, YearAndMonthLocaleFormat::MEDIUM_REVERSE)->toString());
    }
}
