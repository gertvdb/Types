<?php

declare(strict_types=1);

namespace Gertvdb\Types\Float;

use OutOfRangeException;
use PHPUnit\Framework\TestCase;

final class BoundedFloatValueTest extends TestCase
{
    public function testCreateWithinRangeAndAccessors(): void
    {
        $range = new FloatRange(1.0, 2.0);
        $bv = BoundedFloatValue::create(1.5, $range);

        $this->assertSame(1.5, $bv->toFloat());
        $this->assertSame(1.5, $bv->toFloatValue()->toFloat());
        $this->assertSame($range->min()->toFloat(), $bv->range()->min()->toFloat());
        $this->assertSame($range->max()->toFloat(), $bv->range()->max()->toFloat());
    }

    public function testCreateBelowRangeThrows(): void
    {
        $range = new FloatRange(1.0, 2.0);
        $this->expectException(OutOfRangeException::class);
        BoundedFloatValue::create(0.5, $range);
    }

    public function testCreateAboveRangeThrows(): void
    {
        $range = new FloatRange(1.0, 2.0);
        $this->expectException(OutOfRangeException::class);
        BoundedFloatValue::create(2.5, $range);
    }
}
