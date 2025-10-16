<?php

declare(strict_types=1);

namespace Gertvdb\Types\DateTime;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class DateOnlyRangeTest extends TestCase
{
    public function testConstructValid(): void
    {
        $min = DateOnly::from(2024, 1, 1);
        $max = DateOnly::from(2024, 12, 31);
        $range = new DateOnlyRange($min, $max);
        $this->assertSame($min->toString(), $range->min()->toString());
        $this->assertSame($max->toString(), $range->max()->toString());
    }

    public function testConstructInvalidWhenMinGreaterThanMax(): void
    {
        $min = DateOnly::from(2025, 1, 1);
        $max = DateOnly::from(2024, 12, 31);
        $this->expectException(InvalidArgumentException::class);
        new DateOnlyRange($min, $max);
    }
}
