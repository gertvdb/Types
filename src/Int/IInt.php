<?php

// To check : https://github.com/edgaralexanderfr/php-types?tab=readme-ov-file

declare(strict_types=1);

namespace Gertvdb\Types\Int;

interface IInt
{
    public function toIntValue(): IntValue;
    public function toInt(): int;
}
