<?php

declare(strict_types=1);

namespace Gertvdb\Types\Array\HashSet;

use Gertvdb\Types\Int\IntValue;
use Gertvdb\Types\Sorting\SortDirection;
use Gertvdb\Types\Sorting\SortOrder;
use PHPUnit\Framework\TestCase;

final class HashSetTest extends TestCase
{
    public function testEmptyFromArrayAndUniqueness(): void
    {
        $set = HashSet::empty(IntValue::class);
        $this->assertTrue($set->isEmpty());
        $this->assertSame(0, $set->count());

        $a = IntValue::fromInt(2);
        $b = IntValue::fromInt(2); // same hash as $a
        $c = IntValue::fromInt(3);

        $set2 = HashSet::fromArray([$a, $b, $c], IntValue::class);
        // b is duplicate by hash
        $this->assertSame(2, $set2->count());
        $arr = $set2->toArray();
        $this->assertArrayHasKey($a->toHash(), $arr);
        $this->assertArrayHasKey($c->toHash(), $arr);
    }

    public function testAddRemoveHas(): void
    {
        $set = HashSet::empty(IntValue::class);
        $a = IntValue::fromInt(10);
        $b = IntValue::fromInt(20);

        $set1 = $set->add($a);
        $this->assertTrue($set1->has($a));
        $this->assertFalse($set->has($a)); // immutability

        $set2 = $set1->add($b);
        $this->assertSame(2, $set2->count());

        $set3 = $set2->remove($a);
        $this->assertFalse($set3->has($a));
        $this->assertTrue($set3->has($b));
    }

    public function testMerge(): void
    {
        $a = IntValue::fromInt(1);
        $b = IntValue::fromInt(2);
        $c = IntValue::fromInt(3);

        $s1 = HashSet::fromArray([$a, $b], IntValue::class);
        $s2 = HashSet::fromArray([$b, $c], IntValue::class);

        $merged = $s1->merge($s2);
        $this->assertSame(3, $merged->count());
        $this->assertTrue($merged->has($a));
        $this->assertTrue($merged->has($b));
        $this->assertTrue($merged->has($c));
    }

    public function testMapPreservesType(): void
    {
        $s = HashSet::fromArray([IntValue::fromInt(1), IntValue::fromInt(2)], IntValue::class);
        $s2 = $s->map(fn (IntValue $v): IntValue => IntValue::fromInt($v->toInt() * 2));
        $this->assertSame([2, 4], array_values(array_map(fn (IntValue $v) => $v->toInt(), $s2->toArray())));
    }

    public function testSortWithIntComparatorAndSortOrder(): void
    {
        $s = HashSet::fromArray([
            IntValue::fromInt(3),
            IntValue::fromInt(1),
            IntValue::fromInt(2),
        ], IntValue::class);

        $asc = $s->sort(fn (IntValue $a, IntValue $b): int => $a->toInt() <=> $b->toInt());
        $this->assertSame([1, 2, 3], array_values(array_map(fn (IntValue $v) => $v->toInt(), $asc->toArrayValue()->values())));

        $desc = $s->sort(function (IntValue $a, IntValue $b): SortOrder {
            return SortDirection::apply(
                SortOrder::fromComparison(($a->toInt() <=> $b->toInt())),
                SortDirection::DESC
            );
        });
        $this->assertSame([3, 2, 1], array_values(array_map(fn (IntValue $v) => $v->toInt(), $desc->toArrayValue()->values())));
    }

    public function testIterationYieldsValues(): void
    {
        $s = HashSet::fromArray([IntValue::fromInt(1), IntValue::fromInt(2)], IntValue::class);
        $iter = [];
        foreach ($s as $v) {
            $iter[] = $v->toInt();
        }
        sort($iter);
        $this->assertSame([1, 2], $iter);
    }
}
