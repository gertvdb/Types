<?php

declare(strict_types=1);

namespace Gertvdb\Types\Array\Array;

use InvalidArgumentException;
use OutOfRangeException;
use PHPUnit\Framework\TestCase;

final class FixedArrayTest extends TestCase
{
    public function testFromArrayCountIsEmptyAndIteration(): void
    {
        $fa = FixedArray::fromArray([1, 2, 3], 'int');
        $this->assertSame(3, $fa->count());
        $this->assertFalse($fa->isEmpty());

        $iter = [];
        foreach ($fa as $v) {
            $iter[] = $v;
        }
        $this->assertSame([1, 2, 3], $iter);
    }

    public function testGetSetAndBoundsAndTypeChecks(): void
    {
        $fa = FixedArray::fromArray([1, 2, 3], 'int');
        $this->assertSame(2, $fa->get(1));

        $fa2 = $fa->set(1, 42);
        $this->assertSame([1, 42, 3], $fa2->toArray());
        // immutability
        $this->assertSame([1, 2, 3], $fa->toArray());

        $this->expectException(OutOfRangeException::class);
        $fa->get(3);
    }

    public function testSetWrongTypeThrows(): void
    {
        $fa = FixedArray::fromArray([1, 2, 3], 'int');
        $this->expectException(InvalidArgumentException::class);
        $fa->set(0, 'x');
    }
}
