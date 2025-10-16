<?php

declare(strict_types=1);

namespace Gertvdb\Types\DateTime\Formats;

/**
 * https://unicode-org.github.io/icu/userguide/format_parse/datetime/#date-field-symbol-table
 */
enum DateTimeLocaleFormat: string
{
    /** Full datetime format (e.g., October 18, 2024 13:25:45) */
    case DATETIME_FULL = "DATETIME_FULL";

    public function pattern(): string
    {
        return match ($this) {
            self::DATETIME_FULL => "MMMM dd, yyyy HH:mm:ss",
        };
    }
}
