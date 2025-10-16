<?php

declare(strict_types=1);

namespace Gertvdb\Types\DateTime\Formats;

enum NanoSecondFormat: string
{
    /** Format in numeric value (e.g., 341) */
    case NUMERIC = 'numeric';

    /** Format in nine digit value (e.g., 0OOOOO341) */
    case NINE_DIGIT = 'nine_digit';
}
