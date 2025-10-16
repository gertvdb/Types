<?php

declare(strict_types=1);

namespace Gertvdb\Types\String;

interface IString extends \Stringable
{
    public function toStringValue(): StringValue;
    public function toString(): string; // Alias for magic __toString() method.
}
