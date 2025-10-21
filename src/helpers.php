<?php

declare(strict_types=1);

namespace Gertvdb\Types;

use Gertvdb\Types\Array\IHashable;
use Stringable;

function hash(IHashable|int|string|Stringable $item): string
{
    return $item instanceof IHashable ? $item->toHash() : $item;
}

function isOfType(mixed $value, string $type): bool
{
    return match ($type) {
        'int' => is_int($value),
        'string' => is_string($value),
        'float' => is_float($value),
        'bool' => is_bool($value),
        'array' => is_array($value),
        'object' => is_object($value),
        default => $value instanceof $type,
    };
}
