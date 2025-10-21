<?php

declare(strict_types=1);

namespace Gertvdb\Types\DateTime\Formats;

use Gertvdb\Types\I18n\Locale;

enum TimeLocaleFormat: string
{
    case SHORT = "SHORT";
    case WITH_SECONDS = "WITH_SECONDS";

    public function pattern(Locale $locale): string
    {
        // 12-hour locales
        $twelveHour = ['en_US', 'en_CA', 'en_PH', 'en_IN'];

        return match ($this) {
            self::SHORT => in_array($locale->toString(), $twelveHour, true) ? 'hh:mm a' : 'HH:mm',
            self::WITH_SECONDS => in_array($locale->toString(), $twelveHour, true) ? 'hh:mm:ss a' : 'HH:mm:ss',
        };
    }
}
