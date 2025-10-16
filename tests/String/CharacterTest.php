<?php

declare(strict_types=1);

namespace Gertvdb\Types\String;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class CharacterTest extends TestCase
{
    public function testFromStringWithAsciiCharacter(): void
    {
        $char = Character::fromString('a');
        $this->assertSame('a', (string)$char);
        $this->assertSame('a', $char->toString());
        $this->assertSame('a', $char->toStringValue()->toString());
    }

    public function testFromStringWithMultibyteLatinCharacter(): void
    {
        $char = Character::fromString('ö'); // U+00F6 LATIN SMALL LETTER O WITH DIAERESIS
        $this->assertSame('ö', (string)$char);
        $this->assertTrue($char->equals(Character::fromString('ö')));
        $this->assertFalse($char->equals(Character::fromString('Ö'))); // case-sensitive
    }

    public function testFromStringWithEmojiCharacter(): void
    {
        $char = Character::fromString('😊'); // U+1F60A SMILING FACE WITH SMILING EYES
        $this->assertSame('😊', (string)$char);
        $this->assertTrue($char->equals(Character::fromString('😊')));
    }

    public function testEqualsDifferentCharacters(): void
    {
        $a = Character::fromString('a');
        $b = Character::fromString('b');
        $this->assertFalse($a->equals($b));
    }

    public function testInvalidEmptyStringThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Character::fromString('   '); // normalized to empty by StringValue
    }

    public function testInvalidMultiCharacterStringThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Character::fromString('ab');
    }

    public function testInvalidCombiningSequenceThrows(): void
    {
        // "e" + COMBINING ACUTE ACCENT results in two code points; mb_strlen(..., 'UTF-8') !== 1
        $combining = "e\u{0301}";
        $this->expectException(InvalidArgumentException::class);
        Character::fromString($combining);
    }
}
