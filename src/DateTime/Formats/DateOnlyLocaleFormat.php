<?php

declare(strict_types=1);

namespace Gertvdb\Types\DateTime\Formats;

/**
 * https://unicode-org.github.io/icu/userguide/format_parse/datetime/#date-field-symbol-table
 */
enum DateOnlyLocaleFormat: string
{
    /** Full month name with day and year (e.g., October 18, 2024) */
    case DATEONLY_FULL = "DATEONLY_FULL";

    /** Day and month only (e.g., October 18) */
    case DATEONLY_MONTH_DAY = "DATEONLY_MONTH_DAY";

    /** Day and month only (e.g., Oct 18) */
    case DATEONLY_MONTH_DAY_SHORT = "DATEONLY_MONTH_DAY_SHORT";

    /** Day and month only (e.g., 18 October) */
    case DATEONLY_DAY_MONTH = "DATEONLY_DAY_MONTH";

    /** Day and month only (e.g., 18 Oct) */
    case DATEONLY_DAY_MONTH_SHORT = "DATEONLY_DAY_MONTH_SHORT";

    public function pattern(): string
    {
        return match ($this) {
            self::DATEONLY_FULL => 'MMMM dd, yyyy',
            self::DATEONLY_MONTH_DAY => 'MMMM dd',
            self::DATEONLY_MONTH_DAY_SHORT => 'MMM dd',
            self::DATEONLY_DAY_MONTH => 'dd MMMM',
            self::DATEONLY_DAY_MONTH_SHORT => 'dd MMM',
        };
    }
}
