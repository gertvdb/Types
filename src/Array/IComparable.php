<?php

declare(strict_types=1);

namespace Gertvdb\Types\Array;

use Gertvdb\Types\Order\Compare;

interface IComparable
{
    public function compareTo(self $other): Compare;
}
