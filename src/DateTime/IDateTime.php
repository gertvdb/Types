<?php

declare(strict_types=1);

namespace Gertvdb\Types\DateTime;

use DateTimeImmutable;

interface IDateTime
{
    public function toDateTimeValue(): DateTime;
    public function toDateTimeImmutable(): DateTimeImmutable;
}
