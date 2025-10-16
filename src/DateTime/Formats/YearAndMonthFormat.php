<?php

declare(strict_types=1);

namespace Gertvdb\Types\DateTime\Formats;

enum YearAndMonthFormat: string
{
    case ISO = 'ISO';
    case MONTH_YEAR = 'MONTH_YEAR';
    case YEAR_MONTH = 'YEAR_MONTH';

    public function pattern(): string
    {
        return match ($this) {
            self::ISO => "yyyy-MM",
            self::MONTH_YEAR => "MM/yyyy",
            self::YEAR_MONTH => "yyyy/MM",
        };
    }
}
