<?php

declare(strict_types=1);

namespace Gertvdb\Types\Array\HashSet;

use Gertvdb\Types\Sorting\SortDirection;
use Gertvdb\Types\Sorting\SortOrder;
use PHPUnit\Framework\TestCase;

final class ScalarHashSetTest extends TestCase
{
    public function testEmptyFromArrayAndUniquenessForInts(): void
    {
        $set = ScalarHashSet::empty('int');
        $this->assertTrue($set->isEmpty());

        $set2 = ScalarHashSet::fromArray([1, 2, 2, 3], 'int');
        $this->assertSame(3, $set2->count());
        $this->assertTrue($set2->has(1));
        $this->assertTrue($set2->has(2));
        $this->assertTrue($set2->has(3));
    }

    public function testAddRemoveHasForStrings(): void
    {
        $set = ScalarHashSet::empty('string');
        $s1 = $set->add('a')->add('b');
        $this->assertTrue($s1->has('a'));
        $this->assertTrue($s1->has('b'));
        $this->assertFalse($set->has('a')); // immutability

        $s2 = $s1->remove('a');
        $this->assertFalse($s2->has('a'));
        $this->assertTrue($s2->has('b'));
    }

    public function testMerge(): void
    {
        $s1 = ScalarHashSet::fromArray([1, 2], 'int');
        $s2 = ScalarHashSet::fromArray([2, 3], 'int');
        $merged = $s1->merge($s2);
        $this->assertSame(3, $merged->count());
        $this->assertTrue($merged->has(1));
        $this->assertTrue($merged->has(2));
        $this->assertTrue($merged->has(3));
    }

    public function testMapPreservesType(): void
    {
        $s = ScalarHashSet::fromArray([1, 2], 'int');
        $s2 = $s->map(fn (int $n): int => $n * 2);
        $this->assertTrue($s2->has(2));
        $this->assertTrue($s2->has(4));
    }

    public function testSortWithIntComparatorAndSortOrder(): void
    {
        $s = ScalarHashSet::fromArray([3, 1, 2], 'int');

        $asc = $s->sort(fn (int $a, int $b): int => $a <=> $b);
        $this->assertSame([1, 2, 3], $asc->toArrayValue()->values());

        $desc = $s->sort(function (int $a, int $b): SortOrder {
            return SortDirection::apply(
                SortOrder::fromComparison($a <=> $b),
                SortDirection::DESC
            );
        });
        $this->assertSame([3, 2, 1], $desc->toArrayValue()->values());
    }

    public function testIterationYieldsValues(): void
    {
        $s = ScalarHashSet::fromArray(['x', 'y'], 'string');
        $iter = [];
        foreach ($s as $v) {
            $iter[] = $v;
        }
        sort($iter);
        $this->assertSame(['x', 'y'], $iter);
    }
}
