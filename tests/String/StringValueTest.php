<?php

declare(strict_types=1);

namespace Gertvdb\Types\String;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class StringValueTest extends TestCase
{
    public function testFromStringNormalizesAndCasts(): void
    {
        $sv = StringValue::fromString("  Hello World  ");
        $this->assertSame('Hello World', (string)$sv);
        $this->assertSame('Hello World', $sv->toString());
        $this->assertSame($sv, $sv->toStringValue()); // returns same instance
    }

    public function testEmptyStringAfterTrimThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        StringValue::fromString("   \t\n ");
    }

    public function testFromInt(): void
    {
        $sv = StringValue::fromInt(123);
        $this->assertSame('123', (string)$sv);
    }

    public function testEqualsStrictAndNonStrict(): void
    {
        $a = StringValue::fromString('Hello');
        $b = StringValue::fromString('Hello');
        $c = StringValue::fromString('hello');

        $this->assertTrue($a->equals($b));
        $this->assertFalse($a->equals($c));
        $this->assertFalse($a->equals($c, false));
    }

    public function testIsNumeric(): void
    {
        $num = StringValue::fromString('123.45');
        $not = StringValue::fromString('12a');
        $this->assertTrue($num->isNumeric());
        $this->assertFalse($not->isNumeric());
    }

    public function testLowercaseUppercaseWithMultibyte(): void
    {
        $sv = StringValue::fromString('Ää');
        $this->assertSame('ää', (string)$sv->lowercase());
        $this->assertSame('ÄÄ', (string)$sv->uppercase());
    }

    public function testContainsAndLengthWithMultibyte(): void
    {
        $sv = StringValue::fromString("🙂a");
        $this->assertTrue($sv->contains('a'));
        $this->assertFalse($sv->contains('b'));
        $this->assertSame(2, $sv->length());
    }

    public function testBeforeAfterWhenPresent(): void
    {
        $sv = StringValue::fromString('foo=bar=baz');
        $this->assertSame('foo', (string)$sv->before('='));
        $this->assertSame('ar=baz', (string)$sv->after('b')); // after first 'b'
    }

    public function testBeforeAfterWhenAtPositionZeroThrows(): void
    {
        $sv = StringValue::fromString('=value');
        $this->expectException(InvalidArgumentException::class);
        $sv->before('=');
    }

    public function testPrependAppendWithSeparator(): void
    {
        $sv = StringValue::fromString('world');
        $this->assertSame('hello,world', (string)$sv->prepend('hello', ','));
        $this->assertSame('world,hello', (string)$sv->append('hello', ','));
    }

    public function testMaskPositiveIndexAndLength(): void
    {
        $sv = StringValue::fromString('abcdef');
        $masked = $sv->mask(Character::fromString('*'), 2, 3);
        $this->assertSame('ab***f', (string)$masked);
    }

    public function testMaskNegativeIndexCoversFromEnd(): void
    {
        $sv = StringValue::fromString('abcdef');
        $masked = $sv->mask(Character::fromString('#'), -2);
        $this->assertSame('abcd##', (string)$masked);
    }

    public function testMaskNoSegmentReturnsUnchanged(): void
    {
        $sv = StringValue::fromString('abc');
        $masked = $sv->mask(Character::fromString('*'), 100, 5);
        $this->assertSame((string)$sv, (string)$masked);
    }

    public function testReplace(): void
    {
        $sv = StringValue::fromString('hello world');
        $this->assertSame('hello there', (string)$sv->replace('world', 'there'));
    }

    public function testStartsWithEndsWith(): void
    {
        $sv = StringValue::fromString('foobar');
        $this->assertTrue($sv->startsWith('foo'));
        $this->assertFalse($sv->startsWith(''));
        $this->assertTrue($sv->endsWith('bar'));
        $this->assertFalse($sv->endsWith(''));
    }

    public function testTrimLeftTrimRight(): void
    {
        $sv = StringValue::fromString('trim');
        $left = StringValue::fromString('   trim');
        $right = StringValue::fromString('trim   ');
        $this->assertSame((string)$sv, (string)$left->trimLeft());
        $this->assertSame((string)$sv, (string)$right->trimRight());

        $customLeft = StringValue::fromString('---x');
        $this->assertSame('x', (string)$customLeft->trimLeft('-'));

        $customRight = StringValue::fromString('x---');
        $this->assertSame('x', (string)$customRight->trimRight('-'));
    }

    public function testPadLeftPadRight(): void
    {
        $sv = StringValue::fromString('x');
        $this->assertSame('..x', (string)$sv->padLeft(3, '.'));
        $this->assertSame('x..', (string)$sv->padRight(3, '.'));
    }

    public function testSubstrVariants(): void
    {
        $sv = StringValue::fromString('abcdef');
        $this->assertSame('cde', (string)$sv->substr(2, 3));
        $this->assertSame('ef', (string)$sv->substr(-2));
    }
}
