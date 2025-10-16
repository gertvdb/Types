<?php

declare(strict_types=1);

namespace Gertvdb\Types\DateTime\Formats;

enum YearAndMonthLocaleFormat: string
{
    case FULL = 'FULL';
    case MEDIUM = 'MEDIUM';

    case FULL_REVERSE = 'FULL_REVERSE';
    case MEDIUM_REVERSE = 'MEDIUM_REVERSE';

    public function pattern(): string
    {
        return match ($this) {
            self::FULL => "MMMM yyyy",
            self::MEDIUM => "MMM yyyy",
            self::FULL_REVERSE => "yyyy MMMM",
            self::MEDIUM_REVERSE => "yyyy MMM",
        };
    }
}

