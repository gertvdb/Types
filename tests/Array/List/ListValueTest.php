<?php

declare(strict_types=1);

namespace Gertvdb\Types\Array\List;

use Gertvdb\Types\Array\ArrayValue;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class ListValueTest extends TestCase
{
    public function testEmptyFromArrayAddAndIteration(): void
    {
        $l = ListValue::empty('int');
        $this->assertTrue($l->isEmpty());
        $this->assertSame(0, $l->count());

        $l2 = ListValue::fromArray([1, 2, 3], 'int');
        $this->assertSame([1, 2, 3], $l2->toArray());

        $l3 = $l2->add(4);
        $this->assertSame([1, 2, 3, 4], $l3->toArray());

        $iter = [];
        foreach ($l3 as $v) {
            $iter[] = $v;
        }
        $this->assertSame([1, 2, 3, 4], $iter);
    }

    public function testMapFilterReduceFindEachSomeEvery(): void
    {
        $l = ListValue::fromArray([1, 2, 3, 4], 'int');

        $mapped = $l->map(fn (int $n): int => $n * 2);
        $this->assertSame([2, 4, 6, 8], $mapped->toArray());

        $filtered = $l->filter(fn (int $n): bool => $n % 2 === 0);
        $this->assertSame([2, 4], $filtered->toArray());

        $sum = $l->reduce(fn (int $carry, int $n): int => $carry + $n, 0);
        $this->assertSame(10, $sum);

        $found = $l->find(fn (int $n, int $i): bool => $n > 2 && $i === 2);
        $this->assertSame(3, $found);

        $seen = [];
        $l->each(function (int $n, int $i) use (&$seen): void {
            $seen[] = [$i, $n];
        });
        $this->assertSame([[0, 1], [1, 2], [2, 3], [3, 4]], $seen);

        $this->assertTrue($l->some(fn (int $n): bool => $n === 4));
        $this->assertFalse($l->some(fn (int $n): bool => $n === 5));

        $this->assertTrue($l->every(fn (int $n): bool => $n > 0));
        $this->assertFalse($l->every(fn (int $n): bool => $n < 4));
    }

    public function testKeysValuesContainsReverseCountIsEmpty(): void
    {
        $l = ListValue::fromArray([1, 2, 3], 'int');
        $this->assertTrue($l->key_exists(0));
        $this->assertFalse($l->key_exists(3));
        $this->assertTrue($l->contains(2));
        $this->assertFalse($l->contains(5));
        $this->assertSame([0, 1, 2], $l->keys());
        $this->assertSame([1, 2, 3], $l->values());

        $rev = $l->reverse();
        $this->assertSame([3, 2, 1], $rev->toArray());

        $this->assertSame(3, $l->count());
        $this->assertFalse($l->isEmpty());
        $this->assertTrue(ListValue::empty('int')->isEmpty());
    }

    public function testMergeWithArrayAndArrayValueAndTypeEnforcement(): void
    {
        $l = ListValue::fromArray([1, 2], 'int');
        $mergedArr = $l->merge([3, 4]);
        $this->assertSame([1, 2, 3, 4], $mergedArr->toArray());

        $av = ArrayValue::fromArray([5, 6]);
        $mergedVal = $l->merge($av);
        $this->assertSame([1, 2, 5, 6], $mergedVal->toArray());

        $this->expectException(InvalidArgumentException::class);
        $l->merge(['x']);
    }

    public function testAddWrongTypeThrows(): void
    {
        $l = ListValue::empty('int');
        $this->expectException(InvalidArgumentException::class);
        $l->add('x');
    }

    public function testFromArrayReindexesAndConstructorTypeEnforced(): void
    {
        $l = ListValue::fromArray([10 => 'a', 5 => 'b'], 'string');
        $this->assertSame(['a', 'b'], $l->toArray(), 'fromArray should reindex to list semantics');
        $this->assertSame([0, 1], $l->keys());

        $this->expectException(InvalidArgumentException::class);
        ListValue::fromArray([1, 'x'], 'int');
    }

    public function testMapThatChangesTypeThrows(): void
    {
        $l = ListValue::fromArray([1, 2, 3], 'int');
        $this->expectException(InvalidArgumentException::class);
        $l->map(fn (int $n) => (string) $n);
    }

    public function testFindNotFoundReturnsNull(): void
    {
        $l = ListValue::fromArray([1, 2], 'int');
        $this->assertNull($l->find(fn (int $n) => $n > 5));
    }

    public function testToArrayValueReturnsArrayValue(): void
    {
        $l = ListValue::fromArray([1, 2], 'int');
        $av = $l->toArrayValue();
        $this->assertInstanceOf(ArrayValue::class, $av);
        $this->assertSame($l->toArray(), $av->toArray());
    }

    public function testContainsIsStrict(): void
    {
        $l = ListValue::fromArray([1, 2, 3], 'int');
        $this->assertFalse($l->contains('2'));
        $this->assertTrue($l->contains(2));
    }
}
