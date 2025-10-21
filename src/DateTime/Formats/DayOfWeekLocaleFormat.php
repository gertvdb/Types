<?php

declare(strict_types=1);

namespace Gertvdb\Types\DateTime\Formats;

/**
 * https://unicode-org.github.io/icu/userguide/format_parse/datetime/#date-field-symbol-table
 */
enum DayOfWeekLocaleFormat: string
{
    /** Format as 1 letter (e.g., T) */
    case ONE_LETTER = 'ONE_LETTER';

    /** Format short (e.g., Tu) */
    case SHORT = 'SHORT';

    /** Format medium (e.g., Tue) */
    case MEDIUM = 'MEDIUM';

    /** Format full (e.g., Tuesday) */
    case FULL = 'FULL';

    public function pattern(): string
    {
        return match ($this) {
            self::ONE_LETTER => "EEEEE",
            self::SHORT => "EEEEEE",
            self::MEDIUM => "EEE",
            self::FULL => "EEEE",
        };
    }
}
