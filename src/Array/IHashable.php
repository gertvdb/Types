<?php

declare(strict_types=1);

namespace Gertvdb\Types\Array;

interface IHashable
{
    public function toHash(): string;
}
