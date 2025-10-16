<?php

declare(strict_types=1);

namespace Gertvdb\Types\Boolean;

use Gertvdb\Types\Int\IntValue;
use Gertvdb\Types\String\StringValue;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class BooleanValueTest extends TestCase
{
    public function testFromBooleanAndConversions(): void
    {
        $t = BooleanValue::fromBoolean(true);
        $f = BooleanValue::fromBoolean(false);

        $this->assertTrue($t->toBool());
        $this->assertFalse($f->toBool());

        $this->assertSame(1, $t->toInt());
        $this->assertSame(0, $f->toInt());

        $this->assertSame('true', (string)$t);
        $this->assertSame('false', (string)$f);

        $this->assertSame('true', $t->toString());
        $this->assertSame('false', $f->toString());

        $this->assertInstanceOf(IntValue::class, $t->toIntValue());
        $this->assertSame(1, $t->toIntValue()->toInt());
        $this->assertInstanceOf(IntValue::class, $f->toIntValue());
        $this->assertSame(0, $f->toIntValue()->toInt());

        $this->assertInstanceOf(StringValue::class, $t->toStringValue());
        $this->assertSame('true', (string)$t->toStringValue());

        // toBoolValue returns same instance
        $this->assertSame($t, $t->toBoolValue());
    }

    public function testEquals(): void
    {
        $a = new BooleanValue(true);
        $b = BooleanValue::fromBoolean(true);
        $c = new BooleanValue(false);

        $this->assertTrue($a->equals($b));
        $this->assertFalse($a->equals($c));
    }

    public function testFromStringParsesValidInputs(): void
    {
        $this->assertTrue(BooleanValue::fromString('true')->toBool());
        $this->assertFalse(BooleanValue::fromString('false')->toBool());
        $this->assertTrue(BooleanValue::fromString('1')->toBool());
        $this->assertFalse(BooleanValue::fromString('0')->toBool());

        // Case-insensitive and surrounding spaces handled via StringValue normalization
        $this->assertTrue(BooleanValue::fromString(' TRUE ')->toBool());
        $this->assertFalse(BooleanValue::fromString("\tfalse\n")->toBool());
    }

    public function testFromStringRejectsInvalidInputs(): void
    {
        $this->expectException(InvalidArgumentException::class);
        BooleanValue::fromString('00');
    }

    public function testFromStringRejectsNonBooleanNumeric(): void
    {
        $this->expectException(InvalidArgumentException::class);
        BooleanValue::fromString('2');
    }

    public function testFromStringRejectsRandomText(): void
    {
        $this->expectException(InvalidArgumentException::class);
        BooleanValue::fromString('yes');
    }
}
