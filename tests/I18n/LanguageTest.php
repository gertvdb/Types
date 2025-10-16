<?php

declare(strict_types=1);

namespace Gertvdb\Types\I18n;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class LanguageTest extends TestCase
{
    public function testFromStringAcceptsValidTwoLetterCode(): void
    {
        $lang = Language::fromString('en');
        $this->assertSame('en', (string)$lang);
        $this->assertSame('en', $lang->toString());
        $this->assertSame('en', $lang->toStringValue()->toString());
    }

    public function testFromStringRejectsWrongLength(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Language::fromString('eng');
    }

    public function testFromStringRejectsInvalidCode(): void
    {
        $this->expectException(InvalidArgumentException::class);
        // 'zz' should not be a valid language in ICU data
        Language::fromString('zz');
    }

    public function testFromLocaleExtractsPrimaryLanguage(): void
    {
        $locale = Locale::fromString('en-US');
        $lang = Language::fromLocale($locale);
        $this->assertSame('en', (string)$lang);
    }

    public function testFormatLocaleReturnsLocalizedName(): void
    {
        $lang = Language::fromString('en');
        $english = Locale::fromString('en');
        $displayName = $lang->formatLocale($english);

        $this->assertNotSame('', $displayName);
        // In English locale, the language name for 'en' should be 'English'
        $this->assertStringContainsString('English', $displayName);
    }
}
