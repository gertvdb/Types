<?php

declare(strict_types=1);

namespace Gertvdb\Types\DateTime;

use Gertvdb\Types\DateTime\Formats\YearAndMonthFormat;
use Gertvdb\Types\DateTime\Formats\YearAndMonthLocaleFormat;
use Gertvdb\Types\I18n\Locale;
use Gertvdb\Types\Int\BoundedIntValue;
use Gertvdb\Types\Int\IntRange;
use Gertvdb\Types\Int\IntValue;
use Gertvdb\Types\String\StringValue;
use IntlDateFormatter;

final readonly class YearAndMonth
{
    public Year $year;
    public Month $month;

    private DateTime $dateTime;

    private function __construct(
        int $year,
        int $month
    ) {
        $this->year = Year::fromInt($year);
        $this->month = Month::fromInt($month);

        $this->dateTime = DateTime::from(
            Timezone::ETC_UTC,
            $year,
            $month,
            1,
            12
        );
    }

    public static function fromInt(int $year, int $month): self
    {
        return new self($year, $month);
    }

    public static function fromString(string|\Stringable $year, string|\Stringable $month): self
    {
        $monthAsInt = IntValue::fromString($month);
        $yearAsInt = IntValue::fromString($year);
        return new self($yearAsInt->toInt(), $monthAsInt->toInt());
    }

    public function daysInMonth(): BoundedIntValue
    {
        return BoundedIntValue::create(
            cal_days_in_month(CAL_GREGORIAN, $this->month->toInt(), $this->year->toInt()),
            IntRange::create(28, 31)
        );
    }

    public function firstDayOfMonth(): DateOnly
    {
        return DateOnly::from($this->year->toInt(), $this->month->toInt(), Day::fromInt(1)->toInt());
    }

    public function lastDayOfMonth(): DateOnly
    {
        $lastDay = Day::fromInt($this->daysInMonth()->toInt());
        return DateOnly::from($this->year->toInt(), $this->month->toInt(), $lastDay->toInt());
    }

    public function format(YearAndMonthFormat $format): StringValue
    {
        $native = $this->dateTime->toDateTimeImmutable();
        $tz = Timezone::fromString($native->getTimezone()->getName());

        $formatter = new IntlDateFormatter(
            (string) 'en',
            IntlDateFormatter::NONE,
            IntlDateFormatter::NONE,
            $tz->value,
            IntlDateFormatter::GREGORIAN
        );

        $pattern = $format->pattern();
        $formatter->setPattern($pattern);
        return StringValue::fromString($formatter->format($native));
    }

    /**
     * https://unicode-org.github.io/icu/userguide/format_parse/datetime/#date-field-symbol-table
     */
    public function formatLocale(
        Locale $locale,
        YearAndMonthLocaleFormat $format
    ): StringValue {
        $native = $this->dateTime->toDateTimeImmutable();
        $tz = Timezone::fromString($native->getTimezone()->getName());

        $formatter = new IntlDateFormatter(
            (string) $locale->toString(),
            IntlDateFormatter::NONE,
            IntlDateFormatter::NONE,
            $tz->value,
            IntlDateFormatter::GREGORIAN
        );

        $pattern = $format->pattern();
        $formatter->setPattern($pattern);
        return StringValue::fromString($formatter->format($native));
    }
}
