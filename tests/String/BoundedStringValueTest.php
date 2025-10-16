<?php

declare(strict_types=1);

namespace Gertvdb\Types\String;

use Gertvdb\Types\Int\IntRange;
use OutOfRangeException;
use PHPUnit\Framework\TestCase;

final class BoundedStringValueTest extends TestCase
{
    public function testCreateWithinRangeAscii(): void
    {
        $range = IntRange::create(1, 5);
        $bsv = BoundedStringValue::create('abc', $range);

        $this->assertSame('abc', (string)$bsv);
        $this->assertSame('abc', $bsv->toString());
        $this->assertSame('abc', $bsv->toStringValue()->toString());

        $this->assertSame(1, $bsv->range()->min()->toInt());
        $this->assertSame(5, $bsv->range()->max()->toInt());
    }

    public function testNormalizationBeforeLengthCheck(): void
    {
        $range = IntRange::create(1, 1);
        $bsv = BoundedStringValue::create("  a  ", $range);

        $this->assertSame('a', (string)$bsv);
        $this->assertSame(1, $bsv->range()->min()->toInt());
        $this->assertSame(1, $bsv->range()->max()->toInt());
    }

    public function testMultibyteLengthWithinRange(): void
    {
        // "😊ö" is 2 Unicode characters; ensure mb_strlen is used under the hood.
        $range = IntRange::create(2, 2);
        $bsv = BoundedStringValue::create('😊ö', $range);

        $this->assertSame('😊ö', (string)$bsv);
    }

    public function testTooShortThrows(): void
    {
        $range = IntRange::create(3, 5);
        $this->expectException(OutOfRangeException::class);
        BoundedStringValue::create('ab', $range);
    }

    public function testTooLongThrows(): void
    {
        $range = IntRange::create(1, 2);
        $this->expectException(OutOfRangeException::class);
        BoundedStringValue::create('abc', $range);
    }
}
