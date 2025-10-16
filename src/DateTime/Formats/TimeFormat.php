<?php

declare(strict_types=1);

namespace Gertvdb\Types\DateTime\Formats;

/**
 * https://unicode-org.github.io/icu/userguide/format_parse/datetime/#date-field-symbol-table
 */
enum TimeFormat: string
{
    /** Format short (e.g., 12:00) */
    case SHORT = "SHORT";

    /** Format with seconds (e.g., 12:00:01) */
    case WITH_SECONDS = "WITH_SECONDS";

    /** Format with nanoseconds (e.g., 12:00:01.345) */
    case WITH_NANOSECONDS = "WITH_NANOSECONDS";

    public function pattern(): string
    {
        return match ($this) {
            self::SHORT => "HH:mm",
            self::WITH_SECONDS => "HH:mm:ss",
            self::WITH_NANOSECONDS => "HH:mm:ss.SSSS",
        };
    }
}
