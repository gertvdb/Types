<?php

declare(strict_types=1);

namespace Gertvdb\Types\Order;

enum Compare: int
{
    case Less = -1;
    case Equal = 0;
    case Greater = 1;

    public function reverse(): self
    {
        return match ($this) {
            self::Less => self::Greater,
            self::Greater => self::Less,
            self::Equal => self::Equal,
        };
    }
}
