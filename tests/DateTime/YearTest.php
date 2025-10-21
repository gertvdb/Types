<?php

declare(strict_types=1);

namespace Gertvdb\Types\DateTime;

use Gertvdb\Types\DateTime\DateOnly;
use Gertvdb\Types\DateTime\Formats\YearFormat;
use Gertvdb\Types\DateTime\Year;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class YearTest extends TestCase
{
    public function testFromIntCreatesYear(): void
    {
        $y = Year::fromInt(2025);
        $this->assertSame(2025, $y->toInt());
        $this->assertSame(2025, $y->toIntValue()->toInt());
    }

    public function testFromIntAtBounds(): void
    {
        $min = Year::fromInt(Year::MIN);
        $max = Year::fromInt(Year::MAX);

        $this->assertSame(Year::MIN, $min->toInt());
        $this->assertSame(Year::MAX, $max->toInt());
    }

    public function testYearZeroIsInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Year::fromInt(0);
    }

    public function testFromStringParsesPositiveAndNegative(): void
    {
        $y1 = Year::fromString('1984');
        $y2 = Year::fromString('-44');

        $this->assertSame(1984, $y1->toInt());
        $this->assertSame(-44, $y2->toInt());
    }

    public function testFromStringRejectsZero(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Year::fromString('0');
    }

    public function testIsEqual(): void
    {
        $a = Year::fromInt(2000);
        $b = Year::fromInt(2000);
        $c = Year::fromInt(1999);

        $this->assertTrue($a->isEqual($b));
        $this->assertFalse($a->isEqual($c));
    }

    public function testFormatNumeric(): void
    {
        $y = Year::fromInt(7);
        $this->assertSame('7', $y->format(YearFormat::NUMERIC)->toString());

        $yNeg = Year::fromInt(-7);
        $this->assertSame('-7', $yNeg->format(YearFormat::NUMERIC)->toString());
    }

    public function testFormatTwoDigit(): void
    {
        $y = Year::fromInt(7);
        $this->assertSame('07', $y->format(YearFormat::TWO_DIGIT)->toString());

        $y2 = Year::fromInt(123);
        // Should not truncate, padLeft only ensures minimum width 2
        $this->assertSame('123', $y2->format(YearFormat::TWO_DIGIT)->toString());
    }

    public function testFormatRoman(): void
    {
        $y = Year::fromInt(2024);
        $this->assertSame('MMXXIV', $y->format(YearFormat::ROMAN)->toString());

        $y2 = Year::fromInt(1999);
        $this->assertSame('MCMXCIX', $y2->format(YearFormat::ROMAN)->toString());

        $neg = Year::fromInt(-44);
        $this->assertSame('-XLIV', $neg->format(YearFormat::ROMAN)->toString());

        $one = Year::fromInt(1);
        $this->assertSame('I', $one->format(YearFormat::ROMAN)->toString());
    }

    public function testFirstAndLastDayOfYear(): void
    {
        $y = Year::fromInt(2025);
        $first = $y->firstDayOfYear();
        $last = $y->lastDayOfYear();

        $this->assertSame('2025-01-01', (string) $first);
        $this->assertSame('2025-12-31', (string) $last);
    }
}
