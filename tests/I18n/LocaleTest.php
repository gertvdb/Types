<?php

declare(strict_types=1);

namespace Gertvdb\Types\I18n;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class LocaleTest extends TestCase
{
    public function testFromStringAcceptsHyphenAndUnderscore(): void
    {
        $l1 = Locale::fromString('en-US');
        $this->assertSame('en-US', (string)$l1);

        $l2 = Locale::fromString('en_US');
        $this->assertSame('en_US', (string)$l2);
    }

    public function testFromStringRejectsInvalidLocale(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Locale::fromString('en_');
    }

    public function testFromLanguageWrapsLanguage(): void
    {
        $lang = Language::fromString('en');
        $locale = Locale::fromLanguage($lang);
        $this->assertSame('en', (string)$locale);
    }

    public function testFormatLocaleReturnsReadableName(): void
    {
        $usEnglish = Locale::fromString('en-US');
        $english = Locale::fromString('en');

        $displayName = $usEnglish->formatLocale($english);
        $this->assertNotSame('', $displayName);
        // Typically 'English (United States)' – assert stable parts
        $this->assertStringContainsString('English', $displayName);
    }
}
