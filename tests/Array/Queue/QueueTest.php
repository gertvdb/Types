<?php

declare(strict_types=1);

namespace Gertvdb\Types\Array\Queue;

use PHPUnit\Framework\TestCase;
use UnderflowException;

final class QueueTest extends TestCase
{
    public function testEnqueueDequeuePeekAndCount(): void
    {
        $q = Queue::empty('int');
        $q = $q->enqueue(1)->enqueue(2)->enqueue(3);

        $this->assertSame(3, $q->count());
        $this->assertSame(1, $q->peek());
        $this->assertSame([1, 2, 3], $q->toArray());

        $iterated = [];
        foreach ($q as $v) {
            $iterated[] = $v;
        }
        $this->assertSame([1, 2, 3], $iterated);

        $q2 = $q->dequeue();
        $this->assertSame([2, 3], $q2->toArray());
        $this->assertSame(2, $q2->peek());

        $cleared = $q2->clear();
        $this->assertSame([], $cleared->toArray());
        $this->assertSame(0, $cleared->count());
    }

    public function testDequeueOnEmptyReturnsEmptyQueue(): void
    {
        $q = Queue::empty('int');
        $q2 = $q->dequeue();
        $this->assertSame([], $q2->toArray());
        $this->assertSame(0, $q2->count());
    }

    public function testPeekOnEmptyThrowsUnderflow(): void
    {
        $q = Queue::empty('int');
        $this->expectException(UnderflowException::class);
        $q->peek();
    }

    public function testToArrayValueAndToArray(): void
    {
        $q = Queue::empty('int')->enqueue(1)->enqueue(2);

        $this->assertSame([1, 2], $q->toArrayValue()->toArray());
        $this->assertSame([1, 2], $q->toArray());
    }
}
