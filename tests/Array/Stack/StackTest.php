<?php

declare(strict_types=1);

namespace Gertvdb\Types\Array\Stack;

use Gertvdb\Types\Array\ArrayValue;
use PHPUnit\Framework\TestCase;
use UnderflowException;

final class StackTest extends TestCase
{
    public function testPushPopPeekAndCount(): void
    {
        $s = Stack::empty('int');
        $s = $s->push(1)->push(2)->push(3);

        $this->assertSame(3, $s->count());
        $this->assertSame(3, $s->peek());
        $this->assertSame([3, 2, 1], $s->toArray());

        $iterated = [];
        foreach ($s as $v) {
            $iterated[] = $v;
        }
        $this->assertSame([3, 2, 1], $iterated);

        $s2 = $s->pop();
        $this->assertSame([2, 1], $s2->toArray());
        $this->assertSame(2, $s2->peek());
    }

    public function testPopOnEmptyReturnsEmptyStack(): void
    {
        $s = Stack::empty('int');
        $s2 = $s->pop();
        $this->assertSame([], $s2->toArray());
        $this->assertSame(0, $s2->count());
    }

    public function testPeekOnEmptyThrowsUnderflow(): void
    {
        $s = Stack::empty('int');
        $this->expectException(UnderflowException::class);
        $s->peek();
    }

    public function testToArrayValueAndToArray(): void
    {
        $s = Stack::empty('int')->push(1)->push(2);

        $this->assertSame([2, 1], $s->toArrayValue()->toArray());
        $this->assertSame([2, 1], $s->toArray());
    }
}
