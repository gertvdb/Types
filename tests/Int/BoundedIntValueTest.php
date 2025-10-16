<?php

declare(strict_types=1);

namespace Gertvdb\Types\Int;

use OutOfRangeException;
use PHPUnit\Framework\TestCase;

final class BoundedIntValueTest extends TestCase
{
    public function testCreateWithinRangeAndBoundaries(): void
    {
        $range = new IntRange(1, 3);
        $v1 = BoundedIntValue::create(1, $range);
        $v2 = BoundedIntValue::create(2, $range);
        $v3 = BoundedIntValue::create(3, $range);

        $this->assertSame(1, $v1->toInt());
        $this->assertSame(2, $v2->toInt());
        $this->assertSame(3, $v3->toInt());

        $this->assertTrue($range->contains($v1->toIntValue()));
        $this->assertTrue($range->contains($v2->toIntValue()));
        $this->assertTrue($range->contains($v3->toIntValue()));
    }

    public function testCreateOutsideRangeThrows(): void
    {
        $range = new IntRange(10, 20);
        $this->expectException(OutOfRangeException::class);
        BoundedIntValue::create(9, $range);
    }

    public function testRangeAccessor(): void
    {
        $range = new IntRange(5, 7);
        $v = BoundedIntValue::create(6, $range);
        $this->assertSame($range, $v->range());
    }
}
