<?php

declare(strict_types=1);

namespace Gertvdb\Types\DateTime;

use Gertvdb\Types\DateTime\Formats\DateOnlyFormat;
use Gertvdb\Types\DateTime\Formats\DateOnlyLocaleFormat;
use Gertvdb\Types\I18n\Locale;
use Gertvdb\Types\String\IString;
use Gertvdb\Types\String\StringValue;

use Brick\DateTime\DateTimeException;
use Brick\DateTime\LocalDate as InternalDateOnly;
use Error;
use Stringable;

final readonly class DateOnly implements IString
{
    private InternalDateOnly $dateOnly;
    public Year $year;
    public Month $month;
    public YearAndMonth $yearAndMonth;
    public Day $day;
    public DayOfWeek $dayOfWeek;

    private function __construct(
        int $year,
        int $month,
        int $day
    )
    {
        try {
            // Validate and create LocalDate (immutable)
            $this->dateOnly = InternalDateOnly::of(
                year: $year,
                month: $month,
                day: $day
            );
        } catch (DateTimeException $exception) {
            throw new Error('Invalid date format');
        }

        $this->year = Year::fromInt($year);
        $this->month = Month::fromInt($month);
        $this->yearAndMonth = YearAndMonth::fromInt($year, $month);
        $this->day = Day::fromInt($day);
        $this->dayOfWeek = DayOfWeek::fromInt($this->dateOnly->getDayOfWeek()->value);
    }

    public static function from(int $year, int $month, int $day): self
    {
        return new self(
            year: $year,
            month: $month,
            day: $day
        );
    }

    public static function fromDateTime(DateTime $dateTime, Timezone $timezone): self
    {
        $year = $dateTime->year($timezone);
        $month = $dateTime->month($timezone);
        $day = $dateTime->day($timezone);

        return self::from(
            $year->toInt(),
            $month->toInt(),
            $day->toInt()
        );
    }

    /**
     * Only valid format : YYYY-MM-DD
     */
    public static function fromIso(
        string|Stringable $iso
    ): self
    {
        $casted = (string) $iso;
        $parsed = InternalDateOnly::parse($casted);

        return new self(
            $parsed->getYear(),
            $parsed->getMonthValue(),
            $parsed->getDayOfMonth()
        );
    }

    public static function firstDayOf(int $year, ?int $month): self
    {
        return YearAndMonth::fromInt(
            $year,
            $month ?? 1
        )->firstDayOfMonth();
    }

    public static function lastDayOf(int $year, ?int $month): self
    {
        return YearAndMonth::fromInt(
            $year,
            $month ?? 1
        )->lastDayOfMonth();
    }

    // Comparison
    public function equals(DateOnly $other): bool
    {
        return $this->year->toInt() === $other->year->toInt()
            && $this->month->toInt() === $other->month->toInt()
            && $this->day->toInt() === $other->day->toInt();
    }

    public function isBefore(DateOnly $other): bool
    {
        if ($this->year->toInt() < $other->year->toInt()) {
            return true;
        }

        if ($this->year->toInt() === $other->year->toInt() && $this->month->toInt() < $other->month->toInt()) {
            return true;
        }

        if ($this->year->toInt() === $other->year->toInt() && $this->month->toInt() === $other->month->toInt() && $this->day->toInt() < $other->day->toInt()) {
            return true;
        }

        return false;
    }

    public function isAfter(DateOnly $other): bool
    {
        if ($this->year->toInt() > $other->year->toInt()) {
            return true;
        }

        if ($this->year->toInt() === $other->year->toInt() && $this->month->toInt() > $other->month->toInt()) {
            return true;
        }

        if ($this->year->toInt() === $other->year->toInt() && $this->month->toInt() === $other->month->toInt() && $this->day->toInt() > $other->day->toInt()) {
            return true;
        }

        return false;
    }

    public function formatLocale(DateOnlyLocaleFormat $format, Locale $locale): StringValue {

        /**
         * Internally, every DateOnly instance has a timezone because PHP's DateTime objects
         * always carry a timezone. Although DateOnly represents only a calendar day (without
         * hours, minutes, or seconds), some PHP APIs — like IntlDateFormatter — require a
         * timezone to operate correctly.
         *
         * To avoid introducing errors by changing the day or its timezone, we keep the
         * original timezone intact when formatting the date. This ensures that the formatted
         * output is consistent with the original DateOnly value, even though the time part
         * is irrelevant.
         */
        $formatter = new \IntlDateFormatter(
            (string) $locale,
            \IntlDateFormatter::NONE,
            \IntlDateFormatter::NONE,
            'UTC',
            \IntlDateFormatter::GREGORIAN
        );

        $pattern = $format->pattern();
        $formatter->setPattern($pattern);
        return StringValue::fromString($formatter->format($this->dateOnly->toNativeDateTime()));
    }

    public function format(DateOnlyFormat $format) : StringValue {
        $native = $this->dateOnly->toNativeDateTime();
        $pattern = $format->pattern();
        return StringValue::fromString($native->format($pattern));
    }

    public function __toString(): string
    {
        return $this->dateOnly->toISOString();
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
