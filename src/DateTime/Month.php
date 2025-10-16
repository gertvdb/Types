<?php

declare(strict_types=1);

namespace Gertvdb\Types\DateTime;

use Gertvdb\Types\DateTime\Formats\MonthFormat;
use Gertvdb\Types\DateTime\Formats\MonthLocaleFormat;
use Gertvdb\Types\Int\BoundedIntValue;
use Gertvdb\Types\Int\IInt;
use Gertvdb\Types\Int\IntRange;
use Gertvdb\Types\Int\IntValue;
use Gertvdb\Types\I18n\Locale;
use Gertvdb\Types\String\StringValue;

final readonly class Month implements IInt
{
    public const int MIN = 1;
    public const int MAX = 12;

    private DateTime $dateTime;
    public BoundedIntValue $value;

    private function __construct(int $int)
    {
        $range = IntRange::create(self::MIN, self::MAX);
        $this->value = BoundedIntValue::create($int, $range);

        $this->dateTime = DateTime::from(
            Timezone::ETC_UTC,
            2025,
            $int,
            1,
            12
        );
    }

    public static function fromInt(int $int): self
    {
        return new self($int);
    }

    /**
     * Useful to parse from user input (url or forms).
     */
    public static function fromString(string|\Stringable $string): self
    {
        $asInt = IntValue::fromString($string);
        return new self($asInt->toInt());
    }

    public function isEqual(self $other): bool
    {
        return $this->toInt() === $other->toInt();
    }

    public function format(MonthFormat $format): StringValue
    {
        return match ($format) {
            MonthFormat::NUMERIC => StringValue::fromInt($this->toInt()),
            MonthFormat::TWO_DIGIT => StringValue::fromInt($this->toInt())->padLeft(2, '0'),
        };
    }

    /**
     * https://unicode-org.github.io/icu/userguide/format_parse/datetime/#date-field-symbol-table
     */
    public function formatLocale(
        Locale $locale,
        MonthLocaleFormat $format
    ): StringValue
    {
        $native = $this->dateTime->toDateTimeImmutable();
        $tz = Timezone::fromString($native->getTimezone()->getName());

        $formatter = new \IntlDateFormatter(
            (string) $locale->toString(),
            \IntlDateFormatter::NONE,
            \IntlDateFormatter::NONE,
            $tz->value,
            \IntlDateFormatter::GREGORIAN
        );

        $pattern = $format->pattern();
        $formatter->setPattern($pattern);
        return StringValue::fromString($formatter->format($native));
    }

    public function toInt(): int
    {
        return $this->value->toInt();
    }

    public function toIntValue(): IntValue
    {
        return $this->value->toIntValue();
    }
}
