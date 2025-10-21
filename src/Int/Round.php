<?php

declare(strict_types=1);

namespace Gertvdb\Types\Int;

enum Round: string
{
    case HALF_UP = 'HALF_UP';       // round up on .5 (ceil equivalent)
    case HALF_DOWN = 'HALF_DOWN';   // round down on .5 (floor equivalent)
    case HALF_EVEN = 'HALF_EVEN';   // round to nearest even number on .5
    case HALF_ODD = 'HALF_ODD';     // round to nearest odd number on .5
    case UP = 'UP';                  // always round up (ceil)
    case DOWN = 'DOWN';              // always round down (floor)
}
