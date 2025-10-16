<?php

declare(strict_types=1);

namespace Gertvdb\Types\Float;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class FloatRangeTest extends TestCase
{
    public function testConstructorDefaultsAndAccessors(): void
    {
        $r = new FloatRange(null, null);
        $this->assertSame(FloatValue::MIN, $r->min()->toFloat());
        $this->assertSame(FloatValue::MAX, $r->max()->toFloat());

        $r2 = new FloatRange(1.5, 3.5);
        $this->assertSame(1.5, $r2->min()->toFloat());
        $this->assertSame(3.5, $r2->max()->toFloat());

        $this->assertTrue($r2->isMin(new FloatValue(1.5)));
        $this->assertTrue($r2->isMax(new FloatValue(3.5)));
    }

    public function testConstructorThrowsWhenMinGreaterThanMax(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new FloatRange(10.0, 5.0);
    }

    public function testFromStringParsesAndDelegatesToConstructor(): void
    {
        $r = FloatRange::fromString('1.5', '2.5');
        $this->assertSame(1.5, $r->min()->toFloat());
        $this->assertSame(2.5, $r->max()->toFloat());

        $r2 = FloatRange::fromString(null, '2.0');
        $this->assertSame(FloatValue::MIN, $r2->min()->toFloat());
        $this->assertSame(2.0, $r2->max()->toFloat());

        $r3 = FloatRange::fromString('1.0', null);
        $this->assertSame(1.0, $r3->min()->toFloat());
        $this->assertSame(FloatValue::MAX, $r3->max()->toFloat());
    }

    public function testContainsIsInclusive(): void
    {
        $r = new FloatRange(1.0, 2.0);
        $this->assertTrue($r->contains(new FloatValue(1.0)));
        $this->assertTrue($r->contains(new FloatValue(1.5)));
        $this->assertTrue($r->contains(new FloatValue(2.0)));
        $this->assertFalse($r->contains(new FloatValue(0.999999999)));
        $this->assertFalse($r->contains(new FloatValue(2.000000001)));
    }

    public function testClamp(): void
    {
        $r = new FloatRange(1.0, 2.0);
        $this->assertSame(1.0, $r->clamp(new FloatValue(0.5))->toFloat());
        $this->assertSame(2.0, $r->clamp(new FloatValue(3.0))->toFloat());
        $this->assertSame(1.5, $r->clamp(new FloatValue(1.5))->toFloat());
    }

    public function testRandomReturnsWithinRange(): void
    {
        $r = new FloatRange(1.0, 1.0001);
        $rand = $r->random();
        $this->assertGreaterThanOrEqual(1.0, $rand->toFloat());
        $this->assertLessThanOrEqual(1.0001, $rand->toFloat());
    }
}
