<?php

declare(strict_types=1);

namespace Gertvdb\Types\DateTime;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class DateTimeRangeTest extends TestCase
{
    public function testConstructValid(): void
    {
        $min = DateTime::from(Timezone::ETC_UTC, 2024, 1, 1, 0, 0, 0);
        $max = DateTime::from(Timezone::ETC_UTC, 2024, 12, 31, 23, 59, 59);
        $range = new DateTimeRange($min, $max);
        $this->assertSame($min->toString(), $range->min()->toString());
        $this->assertSame($max->toString(), $range->max()->toString());
    }

    public function testConstructInvalidWhenMinGreaterThanMax(): void
    {
        $min = DateTime::from(Timezone::ETC_UTC, 2025, 1, 1, 0, 0, 0);
        $max = DateTime::from(Timezone::ETC_UTC, 2024, 12, 31, 23, 59, 59);
        $this->expectException(InvalidArgumentException::class);
        new DateTimeRange($min, $max);
    }
}
