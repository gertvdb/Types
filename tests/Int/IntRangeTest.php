<?php

declare(strict_types=1);

namespace Gertvdb\Types\Int;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Random\RandomException;

final class IntRangeTest extends TestCase
{
    public function testConstructorWithNullDefaultsToFullIntRange(): void
    {
        $range = new IntRange(null, null);
        $this->assertSame(IntValue::MIN, $range->min()->toInt());
        $this->assertSame(IntValue::MAX, $range->max()->toInt());
    }

    public function testConstructorThrowsWhenMinGreaterThanMax(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new IntRange(5, 4);
    }

    public function testFromStringParsesAndDelegates(): void
    {
        $range = IntRange::fromString('10', '20');
        $this->assertSame(10, $range->min()->toInt());
        $this->assertSame(20, $range->max()->toInt());

        $rangeOpenMin = IntRange::fromString(null, '5');
        $this->assertSame(IntValue::MIN, $rangeOpenMin->min()->toInt());
        $this->assertSame(5, $rangeOpenMin->max()->toInt());

        $rangeOpenMax = IntRange::fromString('5', null);
        $this->assertSame(5, $rangeOpenMax->min()->toInt());
        $this->assertSame(IntValue::MAX, $rangeOpenMax->max()->toInt());
    }

    public function testMinMaxAndIdentityChecks(): void
    {
        $range = new IntRange(1, 3);
        $this->assertTrue($range->isMin(IntValue::fromInt(1)));
        $this->assertFalse($range->isMin(IntValue::fromInt(2)));
        $this->assertTrue($range->isMax(IntValue::fromInt(3)));
        $this->assertFalse($range->isMax(IntValue::fromInt(2)));
    }

    public function testContainsIsInclusive(): void
    {
        $range = new IntRange(1, 3);
        $this->assertTrue($range->contains(IntValue::fromInt(1)));
        $this->assertTrue($range->contains(IntValue::fromInt(2)));
        $this->assertTrue($range->contains(IntValue::fromInt(3)));
        $this->assertFalse($range->contains(IntValue::fromInt(0)));
        $this->assertFalse($range->contains(IntValue::fromInt(4)));
    }

    public function testClamp(): void
    {
        $range = new IntRange(10, 20);
        $this->assertSame(10, $range->clamp(IntValue::fromInt(5))->toInt());
        $this->assertSame(15, $range->clamp(IntValue::fromInt(15))->toInt());
        $this->assertSame(20, $range->clamp(IntValue::fromInt(25))->toInt());
    }

    public function testLengthInclusive(): void
    {
        $range = new IntRange(5, 9);
        $this->assertSame(5, $range->length());
    }

    /**
     * @throws RandomException
     */
    public function testRandomValueIsWithinRange(): void
    {
        $range = new IntRange(100, 105);
        for ($i = 0; $i < 50; $i++) {
            $v = $range->randomValue();
            $this->assertGreaterThanOrEqual(100, $v->toInt());
            $this->assertLessThanOrEqual(105, $v->toInt());
        }
    }
}
