<?php

declare(strict_types=1);

namespace Gertvdb\Types\DateTime\Formats;

/**
 * https://unicode-org.github.io/icu/userguide/format_parse/datetime/#date-field-symbol-table
 */
enum DayOfWeekFormat: string
{
    /** Format in numeric value (e.g., 1) */
    case NUMERIC = 'NUMERIC';

    /** Format in two digit value (e.g., 01) */
    case TWO_DIGIT = 'TWO_DIGIT';
}
