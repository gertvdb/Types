<?php

declare(strict_types=1);

namespace Gertvdb\Types\Boolean;

use Gertvdb\Types\Int\IInt;

// Every bool can also be outputted as int so we extend IInt.
interface IBoolean extends IInt
{
    public function toBoolValue(): BooleanValue;
    public function toBool(): bool;
}
