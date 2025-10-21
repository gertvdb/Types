<?php

declare(strict_types=1);

namespace Gertvdb\Types\DateTime\Formats;

/**
 * https://unicode-org.github.io/icu/userguide/format_parse/datetime/#date-field-symbol-table
 */
enum DateOnlyFormat: string
{
    /** ISO 8601 standard format (e.g., 2024-10-18) */
    case DATEONLY_ISO8601 = "DATEONLY_ISO8601";

    /** Format in most European countries (e.g., 18-10-2024) */
    case DATEONLY_DAY_MONTH_YEAR_DASH = "DATEONLY_DAY_MONTH_YEAR_DASH";

    /** Format in UK, France, ... (e.g., 18/10/2024) */
    case DATEONLY_DAY_MONTH_YEAR_SLASH = "DATEONLY_DAY_MONTH_YEAR_SLASH";

    /** Format in USA, with month first (e.g., 10/18/2024) */
    case DATEONLY_MONTH_DAY_YEAR_SLASH = "DATEONLY_MONTH_DAY_YEAR_SLASH";

    /** Format in Germany (e.g., 18.10.2024) */
    case DATEONLY_DAY_MONTH_YEAR_PERIOD = "DATEONLY_DAY_MONTH_YEAR_PERIOD";

    /** Custom format often used in logging (e.g., 20241018) */
    case DATEONLY_COMPACT = "DATEONLY_COMPACT";

    /** Year only (e.g., 2024) */
    case DATEONLY_YEAR_ONLY = "DATEONLY_YEAR_ONLY";

    public function pattern(): string
    {
        return match ($this) {
            self::DATEONLY_ISO8601 => 'Y-m-d',
            self::DATEONLY_DAY_MONTH_YEAR_DASH => 'd-m-Y',
            self::DATEONLY_DAY_MONTH_YEAR_SLASH => 'd/m/Y',
            self::DATEONLY_MONTH_DAY_YEAR_SLASH => 'm/d/Y',
            self::DATEONLY_DAY_MONTH_YEAR_PERIOD => 'd.m.Y',
            self::DATEONLY_COMPACT => 'Ymd',
            self::DATEONLY_YEAR_ONLY => 'Y'
        };
    }
}
