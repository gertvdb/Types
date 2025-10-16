<?php

declare(strict_types=1);

namespace Gertvdb\Types\DateTime\Formats;

enum YearFormat: string
{
    case NUMERIC = 'NUMERIC';
    case TWO_DIGIT = 'TWO_DIGIT';
    case ROMAN = 'ROMAN';
}
