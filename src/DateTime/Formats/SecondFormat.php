<?php

declare(strict_types=1);

namespace Gertvdb\Types\DateTime\Formats;

enum SecondFormat: string
{
    /** Format in numeric value (e.g., 1) */
    case NUMERIC = 'NUMERIC';

    /** Format in two digit value (e.g., 01) */
    case TWO_DIGIT = 'TWO_DIGIT';
}
