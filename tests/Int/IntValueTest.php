<?php

declare(strict_types=1);

namespace Gertvdb\Types\Int;

use Gertvdb\Types\String\StringValue;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class IntValueTest extends TestCase
{
    public function testConstructorAcceptsBounds(): void
    {
        $min = new IntValue(IntValue::MIN);
        $max = new IntValue(IntValue::MAX);
        $this->assertSame(IntValue::MIN, $min->toInt());
        $this->assertSame(IntValue::MAX, $max->toInt());
        $this->assertTrue($min->equals(IntValue::fromInt(IntValue::MIN)));
        $this->assertTrue($max->equals(IntValue::fromInt(IntValue::MAX)));
    }

    public function testFromIntAndToStringValue(): void
    {
        $v = IntValue::fromInt(123);
        $this->assertSame(123, $v->toInt());
        $this->assertSame('123', (string)$v);
        $this->assertSame('123', $v->toString());
        $this->assertSame((string)StringValue::fromString('123'), (string)$v->toStringValue());
        $this->assertSame($v, $v->toIntValue());
    }

    public function testFromStringParsesVariousValidFormats(): void
    {
        $this->assertSame(0, IntValue::fromString('0')->toInt());
        $this->assertSame(0, IntValue::fromString('00')->toInt());
        $this->assertSame(123, IntValue::fromString('123')->toInt());
        $this->assertSame(-123, IntValue::fromString('-123')->toInt());
        $this->assertSame(123, IntValue::fromString('0123')->toInt());

        // Leading spaces are allowed (trimLeft), trailing spaces are not removed
        $this->assertSame(123, IntValue::fromString('   123')->toInt());
    }

    public function testFromStringRejectsInvalidFormats(): void
    {
        $this->expectException(InvalidArgumentException::class);
        IntValue::fromString('12a3');
    }

    public function testFromStringRejectsDecimal(): void
    {
        $this->expectException(InvalidArgumentException::class);
        IntValue::fromString('12.3');
    }

    public function testFromStringRejectsPlusSign(): void
    {
        $this->expectException(InvalidArgumentException::class);
        IntValue::fromString('+3');
    }

    public function testLengthCountsDigits(): void
    {
        $this->assertSame(1, IntValue::fromInt(0)->length());
        $this->assertSame(3, IntValue::fromInt(123)->length());
        $this->assertSame(4, IntValue::fromInt(-123)->length()); // minus sign included via string length
    }

    public function testEquals(): void
    {
        $a = IntValue::fromInt(42);
        $b = IntValue::fromString('042');
        $c = IntValue::fromInt(41);

        $this->assertTrue($a->equals($b));
        $this->assertFalse($a->equals($c));
    }
}
