<?php

declare(strict_types=1);

namespace Gertvdb\Types\Array;

use Countable;
use IteratorAggregate;

/**
 * Generic immutable array-like interface.
 *
 * @template TValue
 * @extends IteratorAggregate<int|string, TValue>
 */
interface IArray extends IteratorAggregate, Countable
{
    /**
     * Returns the ArrayValue representation of this collection.
     *
     * Note: ArrayValue is a generic container of mixed values; call toArray() for typed arrays.
     */
    public function toArrayValue(): ArrayValue;

    /**
     * Returns a native PHP array copy of the collection.
     *
     * @return array<array-key, TValue>
     */
    public function toArray(): array;
}
