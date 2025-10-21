<?php

declare(strict_types=1);

namespace Gertvdb\Types\Array;

use PHPUnit\Framework\TestCase;

final class ArrayValueTest extends TestCase
{
    public function testFromAndToArrayAndToArrayValue(): void
    {
        $arr = ['a' => 1, 'b' => 2];
        $v = ArrayValue::fromArray($arr);
        $this->assertSame($arr, $v->toArray());
        $this->assertSame($v, $v->toArrayValue());
    }

    public function testIterationPreservesOrderOfValues(): void
    {
        $v = ArrayValue::fromArray(['x' => 10, 'y' => 20, 'z' => 30]);
        $iterated = [];
        foreach ($v as $item) {
            $iterated[] = $item;
        }
        $this->assertSame([10, 20, 30], $iterated);
    }

    public function testMapFilterReduce(): void
    {
        $v = ArrayValue::fromArray([1, 2, 3, 4]);

        $mapped = $v->map(fn (int $n): int => $n * 2);
        $this->assertSame([2, 4, 6, 8], $mapped->toArray());

        $filtered = $v->filter(fn (int $n): bool => $n % 2 === 0);
        $this->assertSame([2, 4], $filtered->toArray());

        $sum = $v->reduce(fn (int $carry, int $n): int => $carry + $n, 0);
        $this->assertSame(10, $sum);
    }

    public function testFindSomeEveryEach(): void
    {
        $v = ArrayValue::fromArray([1, 2, 3, 4]);

        $found = $v->find(fn (int $n): bool => $n > 2);
        $this->assertSame(3, $found);

        $this->assertTrue($v->some(fn (int $n): bool => $n === 4));
        $this->assertFalse($v->some(fn (int $n): bool => $n === 5));

        $this->assertTrue($v->every(fn (int $n): bool => $n > 0));
        $this->assertFalse($v->every(fn (int $n): bool => $n < 4));

        $seen = [];
        $v->each(function (int $n, int $k) use (&$seen): void {
            $seen[] = [$k, $n];
        });
        $this->assertSame([[0, 1], [1, 2], [2, 3], [3, 4]], $seen);
    }

    public function testKeyHelpersAndContains(): void
    {
        $v = ArrayValue::fromArray(['a' => 'x', 'b' => 'y']);

        $this->assertTrue($v->key_exists('a'));
        $this->assertFalse($v->key_exists('c'));

        $this->assertSame('a', $v->key_first());
        $this->assertSame('b', $v->array_last());

        $this->assertTrue($v->contains('x'));
        $this->assertFalse($v->contains('z'));

        $this->assertSame(['a', 'b'], $v->keys());
        $this->assertSame(['x', 'y'], $v->values());
    }

    public function testMergeWithArrayAndArrayValue(): void
    {
        $v1 = ArrayValue::fromArray(['a' => 1, 'b' => 2]);
        $mergedWithArray = $v1->merge(['c' => 3]);
        $this->assertSame(['a' => 1, 'b' => 2, 'c' => 3], $mergedWithArray->toArray());

        $v2 = ArrayValue::fromArray(['d' => 4]);
        $mergedWithValue = $v1->merge($v2);
        $this->assertSame(['a' => 1, 'b' => 2, 'd' => 4], $mergedWithValue->toArray());
    }

    public function testReverseCountIsEmpty(): void
    {
        $v = ArrayValue::fromArray(['a' => 1, 'b' => 2, 'c' => 3]);
        $rev = $v->reverse();
        // array_reverse preserves keys by default in PHP
        $this->assertSame(['c' => 3, 'b' => 2, 'a' => 1], $rev->toArray());

        $this->assertSame(3, $v->count());
        $this->assertFalse($v->isEmpty());

        $empty = ArrayValue::fromArray([]);
        $this->assertSame(0, $empty->count());
        $this->assertTrue($empty->isEmpty());
    }
}
