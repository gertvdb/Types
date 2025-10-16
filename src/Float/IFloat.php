<?php

declare(strict_types=1);

namespace Gertvdb\Types\Float;

interface IFloat
{
    public function toFloatValue(): FloatValue;
    public function toFloat(): float;
}
