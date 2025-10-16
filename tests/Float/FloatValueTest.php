<?php

declare(strict_types=1);

namespace Gertvdb\Types\Float;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class FloatValueTest extends TestCase
{
    public function testConstructorWithinBoundsAndCreate(): void
    {
        $min = new FloatValue(FloatValue::MIN);
        $max = new FloatValue(FloatValue::MAX);
        $this->assertSame(FloatValue::MIN, $min->toFloat());
        $this->assertSame(FloatValue::MAX, $max->toFloat());

        $v = FloatValue::create(123.45);
        $this->assertSame(123.45, $v->toFloat());
        $this->assertSame($v, $v->toFloatValue());
    }

    public function testFromStringValidFormats(): void
    {
        $this->assertSame(0.0, FloatValue::fromString('0')->toFloat());
        $this->assertSame(123.0, FloatValue::fromString('123')->toFloat());
        $this->assertSame(-123.0, FloatValue::fromString('-123')->toFloat());
        $this->assertSame(0.0, FloatValue::fromString('0.0')->toFloat());
        $this->assertSame(123.45, FloatValue::fromString('123.45')->toFloat());
        $this->assertSame(-0.123, FloatValue::fromString('-0.123')->toFloat());
    }

    public function testFromStringRejectsInvalidFormats(): void
    {
        $this->expectException(InvalidArgumentException::class);
        FloatValue::fromString('0123');
    }

    public function testFromStringRejectsLeadingZerosInDecimal(): void
    {
        $this->expectException(InvalidArgumentException::class);
        FloatValue::fromString('00.1');
    }

    public function testFromStringRejectsWhitespace(): void
    {
        $this->expectException(InvalidArgumentException::class);
        FloatValue::fromString(' 123 ');
    }

    public function testFromStringRejectsLettersAndMultipleDots(): void
    {
        $this->expectException(InvalidArgumentException::class);
        FloatValue::fromString('12a3');
    }

    public function testFromStringRejectsMultipleDots(): void
    {
        $this->expectException(InvalidArgumentException::class);
        FloatValue::fromString('12.3.4');
    }

    public function testEquals(): void
    {
        $a = new FloatValue(1.5);
        $b = FloatValue::create(1.5);
        $c = new FloatValue(1.5000001);

        $this->assertTrue($a->equals($b));
        $this->assertFalse($a->equals($c));
    }
}
