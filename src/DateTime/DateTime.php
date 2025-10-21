<?php

declare(strict_types=1);

namespace Gertvdb\Types\DateTime;

use Brick\DateTime\DateTimeException;
use Brick\DateTime\Instant;
use Brick\DateTime\LocalDateTime as InternalLocalDateTime;
use Brick\DateTime\TimeZone as InternalTimeZone;
use Brick\DateTime\TimeZoneRegion as InternalTimeZoneRegion;
use Brick\DateTime\ZonedDateTime as InternalZonedDateTime;
use DateTimeImmutable;
use Error;
use Gertvdb\Types\DateTime\Formats\DateTimeFormat;
use Gertvdb\Types\DateTime\Formats\DateTimeLocaleFormat;
use Gertvdb\Types\I18n\Locale;
use Gertvdb\Types\String\IString;
use Gertvdb\Types\String\StringValue;
use InvalidArgumentException;
use Psr\Clock\ClockInterface;
use Stringable;
use Throwable;

/**
 * Internally we use a UTC date, so every input needs a timezone and every output needs a timezone.
 * It is a bit more hassle to get a value but this way we can assure it's always correct and require
 * the user to be explicit.
 */
final readonly class DateTime implements IString, IDateTime
{
    private InternalZonedDateTime $dateTime;

    private function __construct(
        Timezone $timezone,
        int $year,
        int $month,
        int $day,
        int $hour,
        int $minute,
        int $second,
        int $nanoSecond,
    ) {
        $internalTimezone = self::parseTimezone($timezone);
        try {
            $passedDateTime = InternalZonedDateTime::of(
                dateTime: InternalLocalDateTime::of(
                    year: $year,
                    month: $month,
                    day: $day,
                    hour: $hour,
                    minute: $minute,
                    second: $second,
                    nano: $nanoSecond,
                ),
                timeZone: $internalTimezone
            );
            $timestamp = Timestamp::fromInt($passedDateTime->getEpochSecond());
            $this->dateTime = InternalZonedDateTime::ofInstant(Instant::of($timestamp->value->toInt()), InternalTimeZone::utc());
        } catch (DateTimeException $exception) {
            throw new Error(sprintf('Invalid date format : %s', $exception->getMessage()));
        }
    }

    public static function from(
        Timezone $timezone,
        int $year,
        int $month,
        int $day,
        ?int $hour = 0,
        ?int $minute = 0,
        ?int $second = 0,
        ?int $nanoSecond = 0,
    ): self {
        return new self(
            $timezone,
            $year,
            $month,
            $day,
            $hour,
            $minute,
            $second,
            $nanoSecond
        );
    }

    public static function fromTimestamp(
        Timestamp $timestamp,
    ): self {
        $timezone = Timezone::ETC_UTC;
        $dateTime = InternalZonedDateTime::ofInstant(Instant::of($timestamp->value->toInt()), InternalTimeZone::utc());
        return new self(
            $timezone,
            $dateTime->getYear(),
            $dateTime->getMonthValue(),
            $dateTime->getDayOfMonth(),
            $dateTime->getHour(),
            $dateTime->getMinute(),
            $dateTime->getSecond(),
            $dateTime->getNano(),
        );
    }

    /**
     * Only valid format : YYYY-MM-DDTHH:MM:SSZ → with optional fractional seconds
     */
    public static function fromUTC(
        string|Stringable $iso
    ): self {
        $casted = (string) $iso;
        $value = StringValue::fromString($casted);
        $utcChar = 'Z';

        if (!$value->endsWith($utcChar)) {
            throw new InvalidArgumentException(
                sprintf(
                    'The value "%s" does not end with the "%s" identifier for UTC.',
                    $casted,
                    $utcChar
                )
            );
        }

        $value = $value->replace($utcChar, '');
        return self::fromIso($value->toString(), Timezone::ETC_UTC);
    }

    /**
     * Only valid format : YYYY-MM-DDTHH:MM:SS → with optional fractional seconds
     */
    public static function fromIso(
        string|Stringable $iso,
        Timezone $timezone,
    ): self {
        $casted = (string) $iso;
        $parsed = InternalLocalDateTime::parse($casted);

        return new self(
            $timezone,
            $parsed->getYear(),
            $parsed->getMonthValue(),
            $parsed->getDayOfMonth(),
            $parsed->getHour(),
            $parsed->getMinute(),
            $parsed->getSecond(),
            $parsed->getNano()
        );
    }

    public static function now(ClockInterface $clock): self
    {
        return self::fromTimestamp(Timestamp::fromInt($clock->now()->getTimestamp()));
    }

    public function formatLocale(
        DateTimeLocaleFormat $format,
        Locale $locale,
        Timezone $timezone
    ): StringValue {
        $native = $this->dateTime->toNativeDateTime();

        $formatter = new \IntlDateFormatter(
            (string) $locale,
            \IntlDateFormatter::NONE,
            \IntlDateFormatter::NONE,
            $timezone->value,
            \IntlDateFormatter::GREGORIAN
        );

        $pattern = $format->pattern();
        return match ($format) {
            DateTimeLocaleFormat::DATETIME_FULL => StringValue::fromString(
                (static function ($native) use ($formatter, $pattern) {
                    $formatter->setPattern($pattern);
                    return $formatter->format($native);
                })($native)
            ),
        };
    }

    public function format(DateTimeFormat $format, Timezone $timezone): StringValue
    {
        $native = $this->dateTime->toNativeDateTime();

        /**
         * Locale is fixed because all supported formats are
         * purely locale unaware and do not vary with localization.
         * They are unaffected by cultural differences or offsets.
         *
         * We use IntlDateFormatter here for consistency in format with DateTime and DateOnly.
         * @see https://unicode-org.github.io/icu/userguide/format_parse/datetime/#date-field-symbol-table
         **/
        $formatter = new \IntlDateFormatter(
            (string) 'en',
            \IntlDateFormatter::NONE,
            \IntlDateFormatter::NONE,
            $timezone->value,
            \IntlDateFormatter::GREGORIAN
        );

        $pattern = $format->pattern();
        return match ($format) {
            DateTimeFormat::DATETIME_ISO8601 => StringValue::fromString(
                (static function ($native) use ($formatter, $pattern) {
                    $formatter->setPattern($pattern);
                    return $formatter->format($native);
                })($native)
            ),
        };
    }

    public function __toString(): string
    {
        return $this->dateTime->toISOString();
    }

    public function toStringValue(): StringValue
    {
        return StringValue::fromString($this->__toString());
    }

    public function toString(): string
    {
        return $this->__toString();
    }

    public function year(Timezone $timezone): Year
    {
        $switch = $this->switchTimezone($timezone);
        $native = $switch->toNativeDateTime();
        return Year::fromInt((int) $native->format('Y'));
    }

    public function month(Timezone $timezone): Month
    {
        $switch = $this->switchTimezone($timezone);
        $native = $switch->toNativeDateTime();
        return Month::fromInt((int) $native->format('n'));
    }

    public function day(Timezone $timezone): Day
    {
        $switch = $this->switchTimezone($timezone);
        $native = $switch->toNativeDateTime();
        return Day::fromInt((int) $native->format('j'));
    }

    public function yearAndMonth(Timezone $timezone): YearAndMonth
    {
        $switch = $this->switchTimezone($timezone);
        $native = $switch->toNativeDateTime();
        return YearAndMonth::fromInt((int) $native->format('Y'), (int) $native->format('n'));
    }

    public function dayOfWeek(Timezone $timezone): DayOfWeek
    {
        $switch = $this->switchTimezone($timezone);
        $native = $switch->toNativeDateTime();
        return DayOfWeek::fromInt((int) $native->format('N'));
    }

    public function time(Timezone $timezone): Time
    {
        $switch = $this->switchTimezone($timezone);
        $native = $switch->toNativeDateTime();
        return Time::from(
            (int) $native->format('G'),
            (int) $native->format('i'),
            (int) $native->format('s'),
            (int) ($native->format('u') . '000'),
        );
    }

    private function switchTimezone(Timezone $timezone): InternalZonedDateTime
    {
        $internalTimezone = self::parseTimezone($timezone);
        return InternalZonedDateTime::ofInstant(Instant::of($this->dateTime->getEpochSecond()), $internalTimezone);
    }

    private static function parseTimezone(Timezone $timezone): InternalTimeZoneRegion
    {
        try {
            return InternalTimeZoneRegion::parse($timezone->value);
        } catch (\Throwable $e) {
            throw new InvalidArgumentException(sprintf(
                'Invalid timezone "%s": %s',
                $timezone->value,
                $e->getMessage()
            ), 0, $e);
        }
    }

    public function isBefore(DateTime $targetObject): bool
    {
        return $this->dateTime->getEpochSecond() < $targetObject->dateTime->getEpochSecond();
    }

    public function isAfter(DateTime $targetObject): bool
    {
        return $this->dateTime->getEpochSecond() > $targetObject->dateTime->getEpochSecond();
    }

    public function isSameDay(DateTime $targetObject): bool
    {
        return $this->dateTime->getYear() === $targetObject->dateTime->getYear() && $this->dateTime->getMonthValue() === $targetObject->dateTime->getMonthValue() && $this->dateTime->getDayOfMonth() === $targetObject->dateTime->getDayOfMonth();
    }

    public function isFuture(ClockInterface $clock): bool
    {
        $nowUtc = self::now($clock);
        return $this->isAfter($nowUtc);
    }

    public function isPast(ClockInterface $clock): bool
    {
        $nowUtc = self::now($clock);
        return $this->isBefore($nowUtc);
    }

    public function add(
        int $years = 0,
        int $months = 0,
        int $days = 0,
        int $hours = 0,
        int $minutes = 0,
        int $seconds = 0,
    ): self {
        $transformed = $this->dateTime
            ->plusYears($years)
            ->plusMonths($months)
            ->plusDays($days)
            ->plusHours($hours)
            ->plusMinutes($minutes)
            ->plusSeconds($seconds);

        return self::fromTimestamp(Timestamp::fromInt($transformed->getEpochSecond()));
    }

    public function subtract(
        int $years = 0,
        int $months = 0,
        int $days = 0,
        int $hours = 0,
        int $minutes = 0,
        int $seconds = 0,
    ): self {
        $transformed = $this->dateTime
            ->minusYears($years)
            ->minusMonths($months)
            ->minusDays($days)
            ->minusHours($hours)
            ->minusMinutes($minutes)
            ->minusSeconds($seconds);

        return self::fromTimestamp(Timestamp::fromInt($transformed->getEpochSecond()));
    }

    public function timestamp(): Timestamp
    {
        return Timestamp::fromInt($this->dateTime->getEpochSecond());
    }

    public function startOfDay(): self
    {
        $transformed = $this->dateTime->withHour(0)->withMinute(0)->withSecond(0)->withNano(0);
        return self::fromTimestamp(Timestamp::fromInt($transformed->getEpochSecond()));
    }

    public function endOfDay(): self
    {
        $transformed = $this->dateTime->withHour(23)->withMinute(59)->withSecond(59)->withNano(999999999);
        return self::fromTimestamp(Timestamp::fromInt($transformed->getEpochSecond()));
    }

    public function toDateTimeValue(): DateTime
    {
        return $this;
    }

    public function toDateTimeImmutable(): DateTimeImmutable
    {
        return $this->dateTime->toNativeDateTimeImmutable();
    }
}
