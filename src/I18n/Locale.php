<?php

declare(strict_types=1);

namespace Gertvdb\Types\I18n;

use Gertvdb\Types\String\IString;
use Gertvdb\Types\String\StringValue;
use InvalidArgumentException;
use Stringable;
use Symfony\Component\Intl\Locales;

final readonly class Locale implements IString
{
    private StringValue $value;
    private StringValue $phpValue;

    private function __construct(
        StringValue $value
    ) {

        /**
         * In PHP’s intl extension and in Symfony’s Intl component, locales must follow BCP 47 / RFC 5646 format
         * → which means they use underscores (_) instead of hyphens (-) in PHP’s implementation.
         */
        $phpValue = $value->replace('-', '_');

        $isValidLocale = Locales::exists($phpValue->toString());
        if (!$isValidLocale) {
            throw new InvalidArgumentException(sprintf(
                'The value "%s" is not a valid locale.',
                $value->toString(),
            ));
        }

        $this->phpValue = $phpValue;
        $this->value = $value;
    }

    public static function fromString(Stringable|string $value): self
    {
        $casted = (string) $value;
        $str = StringValue::fromString($casted);
        return new self($str);
    }

    public static function fromLanguage(Language $value): self
    {
        return new self($value->toStringValue());
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
        return Locales::getName($this->phpValue->toString(), $locale->toString());
    }
}
