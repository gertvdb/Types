<?php

declare(strict_types=1);

namespace Gertvdb\Types\DateTime;

use Gertvdb\Types\DateTime\Formats\TimeFormat;
use Gertvdb\Types\DateTime\Formats\TimeLocaleFormat;
use Gertvdb\Types\Int\IntValue;
use Gertvdb\Types\Language\Locale;
use Gertvdb\Types\String\StringValue;

final readonly class Time
{

    private DateTime $dateTime;

    public Hour  $hour;
    public Minute $minute;
    public Second $second;
    public NanoSecond $nanoSecond;

    /**
     * Static Constructors
     */
    private function __construct(
         int $hour,
         int $minute,
         ?int $second = 0,
         ?int $nanoSecond = 0
    ) {
        $this->hour = Hour::fromInt($hour);
        $this->minute = Minute::fromInt($minute);
        $this->second = Second::fromInt($second);
        $this->nanoSecond = NanoSecond::fromInt($nanoSecond);

        $this->dateTime = DateTime::from(
            timezone: Timezone::ETC_UTC,
            year:2025,
            month: 1,
            day: 1,
            hour: $hour,
            minute: $minute,
            second: $second,
            nanoSecond: $nanoSecond
        );
    }

    public static function from(
        int $hour,
        int $minute,
        ?int $second = 0,
        ?int $nanoSecond = 0
    ): self {
        return new self(
            $hour,
            $minute,
            $second,
            $nanoSecond,
        );
    }

    public static function fromString(string $timeString): self {

        // Split nano part if present
        $nanoSeconds = 0;
        if (str_contains($timeString, '.')) {
            [$timeString, $nanoPart] = explode('.', $timeString, 2);

            // Normalize, since .100 mean 100 000 000 nanoseconds.
            $normalize = StringValue::fromString($nanoPart)->padRight(9, '0');

            if (!ctype_digit($normalize->toString())) {
                throw new \InvalidArgumentException(
                    'Invalid nano seconds format. Expected digits only.'
                );
            }

            $int = ltrim($normalize->toString(), "0");
            $nanoSeconds = IntValue::fromString($int)->toInt();
        }

        // Split into hour, minute, second
        $parts = explode(':', $timeString);

        if (!in_array(count($parts), [2, 3], true)) {
            throw new \InvalidArgumentException(
                'Invalid time string format. Expected "HH:mm", "HH:mm:ss" or "HH:mm:ss.SSS".'
            );
        }

        // Pad with default values: seconds=0 if missing
        [$hours, $minutes, $seconds] = array_pad(array_map('intval', $parts), 3, 0);

        return self::from($hours, $minutes, $seconds, $nanoSeconds);
    }

    public static function fromNanoseconds(int $nanoseconds): Time
    {
        // Normalize into 24h range if needed
        $nanoseconds %= 24 * 3600 * 1_000_000_000; // total nanoseconds in a day
        if ($nanoseconds < 0) {
            $nanoseconds += 24 * 3600 * 1_000_000_000;
        }

        $hours = intdiv($nanoseconds, 3600 * 1_000_000_000);
        $nanoseconds %= 3600 * 1_000_000_000;

        $minutes = intdiv($nanoseconds, 60 * 1_000_000_000);
        $nanoseconds %= 60 * 1_000_000_000;

        $seconds = intdiv($nanoseconds, 1_000_000_000);
        $nanoseconds %= 1_000_000_000;

        return self::from($hours, $minutes, $seconds, $nanoseconds);
    }

    /**
     * Tooling
     */

    public function isSame(Time $other): bool
    {
        return $this->toNanoseconds() === $other->toNanoseconds();
    }

    public function isBefore(Time $other): bool
    {
        return $this->toNanoseconds() < $other->toNanoseconds();
    }

    public function isAfter(Time $other): bool
    {
        return $this->toNanoseconds() > $other->toNanoseconds();
    }

    public function toNanoseconds(): int
    {
        return (
                ($this->hour->toInt() * 3600 +
                    $this->minute->toInt() * 60 +
                    $this->second->toInt()) * 1000
            ) + $this->nanoSecond->toInt();
    }

    /**
     * Modifiers
     */
    public function add(
        int $hours = 0,
        int $minutes = 0,
        int $seconds = 0,
        int $nanoseconds = 0
    ): Time {
        $deltaNanos =
            ($hours * 3600 +
                $minutes * 60 +
                $seconds) * 1_000_000_000
            + $nanoseconds;

        return self::fromNanoseconds($this->toNanoseconds() + $deltaNanos);
    }

    public function subtract(
        int $hours = 0,
        int $minutes = 0,
        int $seconds = 0,
        int $nanoseconds = 0
    ): Time {
        $deltaNanos =
            ($hours * 3600 +
                $minutes * 60 +
                $seconds) * 1_000_000_000
            + $nanoseconds;

        return self::fromNanoseconds($this->toNanoseconds() - $deltaNanos);
    }

    public function formatLocale(
        TimeLocaleFormat $format,
        Locale $locale
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

        return match ($format) {
            TimeLocaleFormat::SHORT => StringValue::fromString(
                (static function($locale, $native) use ($formatter) {
                    $formatter->setPattern(TimeLocaleFormat::SHORT->pattern($locale));
                    return $formatter->format($native);
                })($locale, $native)
            ),
            TimeLocaleFormat::WITH_SECONDS => StringValue::fromString(
                (static function($locale, $native) use ($formatter) {
                    $formatter->setPattern(TimeLocaleFormat::WITH_SECONDS->pattern($locale));
                    return $formatter->format($native);
                })($locale, $native)
            ),
        };
    }

    /**
     * Output
     */
    public function format(
        TimeFormat $format,
    ): StringValue {
        $native = $this->dateTime->toDateTimeImmutable();
        $tz = Timezone::fromString($native->getTimezone()->getName());

        /**
         * Timezone and locale are fixed because all supported formats are
         * purely time-based and do not vary with localization or timezone.
         * They are unaffected by cultural differences or offsets.
         *
         * We use IntlDateFormatter here for consistency in format with DateTime and DateOnly.
         * @see https://unicode-org.github.io/icu/userguide/format_parse/datetime/#date-field-symbol-table
         **/
        $formatter = new \IntlDateFormatter(
            (string) 'en',
            \IntlDateFormatter::NONE,
            \IntlDateFormatter::NONE,
            $tz->value,
            \IntlDateFormatter::GREGORIAN
        );

        $pattern = $format->pattern();
        $formatter->setPattern($pattern);
        return StringValue::fromString($formatter->format($native));
    }
}
