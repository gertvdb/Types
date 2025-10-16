<?php

declare(strict_types=1);

namespace Gertvdb\Types\DateTime;

use Gertvdb\Types\DateTime\Formats\DayOfWeekFormat;
use Gertvdb\Types\DateTime\Formats\DayOfWeekLocaleFormat;
use Gertvdb\Types\I18n\Locale;
use PHPUnit\Framework\TestCase;

final class DayOfWeekTest extends TestCase
{
    public function testPredicatesAndValues(): void
    {
        $mon = DayOfWeek::fromInt(1);
        $tue = DayOfWeek::fromInt(2);
        $wed = DayOfWeek::fromInt(3);
        $thu = DayOfWeek::fromInt(4);
        $fri = DayOfWeek::fromInt(5);
        $sat = DayOfWeek::fromInt(6);
        $sun = DayOfWeek::fromInt(7);

        $this->assertTrue($mon->isMonday());
        $this->assertTrue($tue->isTuesday());
        $this->assertTrue($wed->isWednesday());
        $this->assertTrue($thu->isThursday());
        $this->assertTrue($fri->isFriday());
        $this->assertTrue($sat->isSaturday());
        $this->assertTrue($sun->isSunday());

        $this->assertFalse($mon->isWeekend());
        $this->assertTrue($sat->isWeekend());
        $this->assertTrue($sun->isWeekend());

        $this->assertSame(1, $mon->toInt());
        $this->assertSame('1', $mon->format(DayOfWeekFormat::NUMERIC)->toString());
        $this->assertSame('01', $mon->format(DayOfWeekFormat::TWO_DIGIT)->toString());
    }

    public function testFormatLocale(): void
    {
        $fri = DayOfWeek::fromInt(5);
        $locale = Locale::fromString('en-US');

        $one = $fri->formatLocale(DayOfWeekLocaleFormat::ONE_LETTER, $locale)->toString();
        $short = $fri->formatLocale(DayOfWeekLocaleFormat::SHORT, $locale)->toString();
        $med = $fri->formatLocale(DayOfWeekLocaleFormat::MEDIUM, $locale)->toString();
        $full = $fri->formatLocale(DayOfWeekLocaleFormat::FULL, $locale)->toString();

        $this->assertNotSame('', $one);
        $this->assertNotSame('', $short);
        $this->assertNotSame('', $med);
        $this->assertNotSame('', $full);

        // basic expectations (English): letter lengths monotonic
        $this->assertTrue(strlen($one) <= strlen($short));
        $this->assertTrue(strlen($short) <= strlen($med) || strlen($short) === 2); // ICU varies
        $this->assertTrue(strlen($med) <= strlen($full));
    }
}
