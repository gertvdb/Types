<?php

declare(strict_types=1);

namespace Gertvdb\Types\DateTime;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class BoundedDateTimeTest extends TestCase
{
    public function testCreateWithinRange(): void
    {
        $min = DateTime::from(Timezone::ETC_UTC, 2024, 1, 1, 0, 0, 0);
        $max = DateTime::from(Timezone::ETC_UTC, 2024, 12, 31, 23, 59, 59);
        $range = new DateTimeRange($min, $max);
        $value = DateTime::from(Timezone::ETC_UTC, 2024, 6, 1, 12, 0, 0);

        $bounded = BoundedDateTime::create($value, $range);
        $this->assertSame($value->toString(), $bounded->value->toString());
        $this->assertSame($min->toString(), $bounded->range->min()->toString());
        $this->assertSame($max->toString(), $bounded->range->max()->toString());
    }

    public function testCreateBelowMinThrows(): void
    {
        $min = DateTime::from(Timezone::ETC_UTC, 2024, 1, 1, 0, 0, 0);
        $max = DateTime::from(Timezone::ETC_UTC, 2024, 12, 31, 23, 59, 59);
        $range = new DateTimeRange($min, $max);
        $value = DateTime::from(Timezone::ETC_UTC, 2023, 12, 31, 23, 59, 59);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('cannot fall before');
        BoundedDateTime::create($value, $range);
    }

    public function testCreateAboveMaxThrows(): void
    {
        $min = DateTime::from(Timezone::ETC_UTC, 2024, 1, 1, 0, 0, 0);
        $max = DateTime::from(Timezone::ETC_UTC, 2024, 12, 31, 23, 59, 59);
        $range = new DateTimeRange($min, $max);
        $value = DateTime::from(Timezone::ETC_UTC, 2025, 1, 1, 0, 0, 0);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('cannot fall after');
        BoundedDateTime::create($value, $range);
    }
}
