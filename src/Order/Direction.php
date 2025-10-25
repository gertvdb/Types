<?php

declare(strict_types=1);

namespace Gertvdb\Types\Order;

enum Direction: string
{
    case ASC = 'ASC';
    case DESC = 'DESC';

    public function reverse(): self
    {
        return $this === self::ASC ? self::DESC : self::ASC;
    }
}
