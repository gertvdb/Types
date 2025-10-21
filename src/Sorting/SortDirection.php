<?php

declare(strict_types=1);

namespace Gertvdb\Types\Sorting;

final class SortDirection
{
    public const string ASC = 'ASC';
    public const string DESC = 'DESC';

    public static function apply(SortOrder $order, string $direction): SortOrder
    {
        return $direction === self::DESC ? $order->reverse() : $order;
    }
}
