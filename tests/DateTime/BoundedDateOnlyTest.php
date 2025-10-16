<?php

declare(strict_types=1);

namespace Gertvdb\Types\DateTime;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class BoundedDateOnlyTest extends TestCase
{
    public function testCreateWithinRange(): void
    {
        $min = DateOnly::from(2024, 1, 1);
        $max = DateOnly::from(2024, 12, 31);
        $range = new DateOnlyRange($min, $max);
        $value = DateOnly::from(2024, 6, 1);

        $bounded = BoundedDateOnly::create($value, $range);
        $this->assertSame($value->toString(), $bounded->value->toString());
        $this->assertSame($min->toString(), $bounded->range->min()->toString());
        $this->assertSame($max->toString(), $bounded->range->max()->toString());
    }

    public function testCreateBelowMinThrows(): void
    {
        $min = DateOnly::from(2024, 1, 1);
        $max = DateOnly::from(2024, 12, 31);
        $range = new DateOnlyRange($min, $max);
        $value = DateOnly::from(2023, 12, 31);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('cannot fall before');
        BoundedDateOnly::create($value, $range);
    }

    public function testCreateAboveMaxThrows(): void
    {
        $min = DateOnly::from(2024, 1, 1);
        $max = DateOnly::from(2024, 12, 31);
        $range = new DateOnlyRange($min, $max);
        $value = DateOnly::from(2025, 1, 1);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('cannot fall after');
        BoundedDateOnly::create($value, $range);
    }
}
