<?php

declare(strict_types=1);

namespace Gertvdb\Types\DateTime\Formats;

/**
 * https://unicode-org.github.io/icu/userguide/format_parse/datetime/#date-field-symbol-table
 */
enum DateTimeFormat: string
{
    /**
     * ISO 8601 standard datetime format.
     * Example: 2024-10-18T15:42:30+02:00
     */
    case DATETIME_ISO8601 = "DATETIME_ISO8601";

    public function pattern(): string
    {
        return match ($this) {
            self::DATETIME_ISO8601 => "yyyy-MM-dd'T'HH:mm:ssxxx",
        };
    }
}
