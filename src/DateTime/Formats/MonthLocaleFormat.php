<?php

declare(strict_types=1);

namespace Gertvdb\Types\DateTime\Formats;

enum MonthLocaleFormat: string
{
    /** Format in one letter (e.g., S) */
    case ONE_LETTER = 'ONE_LETTER';

    /** Format short (e.g., Sep) */
    case SHORT = 'SHORT';

    /** Format full (e.g., September) */
    case FULL = 'FULL';

    public function pattern(): string
    {
        return match ($this) {
            self::ONE_LETTER => "LLLLL",
            self::SHORT => "LLL",
            self::FULL => "LLLL",
        };
    }
}

