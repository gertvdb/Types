<?php

declare(strict_types=1);

namespace Gertvdb\Types\DateTime;

use Gertvdb\Types\DateTime\Formats\DayOfWeekFormat;
use Gertvdb\Types\DateTime\Formats\DayOfWeekLocaleFormat;
use Gertvdb\Types\Int\IInt;
use Gertvdb\Types\Int\IntValue;
use Gertvdb\Types\I18n\Locale;
use Gertvdb\Types\String\StringValue;

final readonly class DayOfWeek implements IInt
{
    private DateTime $dateTime;
    public int $value;

    private function __construct(int $int)
    {
        $this->value = $int;
        $map = [
            1 => DateTime::from(Timezone::ETC_UTC, 2023, 1, 2, 12), // Monday
            2 => DateTime::from(Timezone::ETC_UTC, 2023, 1, 3, 12), // Tuesday
            3 => DateTime::from(Timezone::ETC_UTC, 2023, 1, 4, 12), // Wednesday
            4 => DateTime::from(Timezone::ETC_UTC, 2023, 1, 5, 12), // Thursday
            5 => DateTime::from(Timezone::ETC_UTC, 2023, 1, 6, 12), // Friday
            6 => DateTime::from(Timezone::ETC_UTC, 2023, 1, 7, 12), // Saturday
            7 => DateTime::from(Timezone::ETC_UTC, 2023, 1, 8, 12), // Sunday
        ];

        $this->dateTime = $map[$int];
    }

    public static function fromInt(int $int): self
    {
        return new self($int);
    }

    public function isMonday(): bool
    {
        return $this->value === 1;
    }

    public function isTuesday(): bool
    {
        return $this->value === 2;
    }

    public function isWednesday(): bool
    {
        return $this->value === 3;
    }

    public function isThursday(): bool
    {
        return $this->value === 4;
    }

    public function isFriday(): bool
    {
        return $this->value === 5;
    }

    public function isSaturday(): bool
    {
        return $this->value === 6;
    }

    public function isSunday(): bool
    {
        return $this->value === 7;
    }

    public function isWeekend(): bool
    {
        return $this->isSaturday() || $this->isSunday();
    }

    /**
     * @throws \Error
     */
    public function format(DayOfWeekFormat $format): StringValue
    {
        return match ($format) {
            DayOfWeekFormat::NUMERIC => StringValue::fromInt($this->value),
            DayOfWeekFormat::TWO_DIGIT => StringValue::fromInt($this->value)->padLeft(2, '0'),
        };
    }

    public function formatLocale(
        DayOfWeekLocaleFormat $format,
        Locale $locale
    ): StringValue
    {
        $native = $this->dateTime->toDateTimeImmutable();
        $formatter = new \IntlDateFormatter(
            (string) $locale->toString(),
            \IntlDateFormatter::NONE,
            \IntlDateFormatter::NONE,
            'UTC',
            \IntlDateFormatter::GREGORIAN
        );

        $pattern = $format->pattern();
        $formatter->setPattern($pattern);
        return StringValue::fromString($formatter->format($native));
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }

    public function toIntValue(): IntValue
    {
        return IntValue::fromInt($this->value);
    }

    public function toInt(): int
    {
        return $this->value;
    }

    public function toStringValue(): StringValue
    {
        return StringValue::fromString($this->__toString());
    }

    public function toString(): string
    {
       return $this->__toString();
    }

}
