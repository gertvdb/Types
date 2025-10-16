<?php

declare(strict_types=1);

namespace Gertvdb\Types\I18n;

use Gertvdb\Types\String\IString;
use Gertvdb\Types\String\StringValue;
use InvalidArgumentException;
use Stringable;
use Symfony\Component\Intl\Locales;

final readonly class Language implements IString
{
    private StringValue $value;

    private function __construct(
        StringValue $value
    )
    {
        $length = $value->length();
        if ($length !== 2) {
            throw new InvalidArgumentException(sprintf(
                'The value "%s" is not a valid language code, it can only be 2 characters long.',
                $value->toString(),
            ));
        }

        // a Language only is a valid locale, so if it's a valid locale, it's a valid language.
        $isValidLocale = Locales::exists($value->toString());
        if (!$isValidLocale) {
            throw new InvalidArgumentException(sprintf(
                'The value "%s" is not a valid locale.',
                $value->toString(),
            ));
        }

        $this->value = $value;
    }

    public static function fromString(Stringable|string $value): self
    {
        $casted = (string) $value;
        $str = StringValue::fromString($casted);
        return new self($str);
    }

    public static function fromLocale(Locale $locale): Language
    {
        $language = \Locale::getPrimaryLanguage($locale->toString());
        if (!$language) {
            // Should not happen.
            throw new InvalidArgumentException(sprintf(
                'The language could not be derived for locale "%s".',
                $locale->toString(),
            ));
        }
        return self::fromString($language);
    }

    public function toStringValue(): StringValue
    {
        return $this->value;
    }

    public function toString(): string
    {
        return $this->__toString();
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }

    public function formatLocale(Locale $locale): string
    {
        return Locales::getName($this->toString(), $locale->toString());
    }
}
