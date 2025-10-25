<?php

declare(strict_types=1);

namespace Gertvdb\Types\Array\HashSet;

use Gertvdb\Types\Array\IHashableComparable;
use Gertvdb\Types\Order\Compare;
use PHPUnit\Framework\TestCase;

final class SortedHashSetTest extends TestCase
{
    private static function thing(int $v): Thing
    {
        return new Thing($v);
    }

    public function testAddIterationIsSortedByCompareTo(): void
    {
        $item1 = self::thing(3);
        $item2 = self::thing(1);
        $item3 = self::thing(2);

        $set = SortedHashSet::empty(Thing::class)
            ->add($item1)
            ->add($item2)
            ->add($item3);

        $iterated = [];
        /** @var Thing $t */
        foreach ($set as $t) {
            $iterated[] = $t->value();
        }

        $this->assertSame([1, 2, 3], $iterated);
        $this->assertSame(3, $set->count());

        $this->assertTrue($set->has(self::thing(2)));
        $this->assertFalse($set->has(self::thing(99)));
    }

    public function testRemoveAndMerge(): void
    {
        $item1 = self::thing(3);
        $item2 = self::thing(1);
        $item3 = self::thing(2);
        $item4 = self::thing(4);
        $item5 = self::thing(0);

        $a = SortedHashSet::empty(Thing::class)
            ->add($item1)
            ->add($item2)
            ->add($item3);

        $b = SortedHashSet::empty(Thing::class)
            ->add($item4)
            ->add($item5);

        $a2 = $a->remove($item3);
        $this->assertSame([1, 3], array_map(static fn(Thing $t) => $t->value(), iterator_to_array($a2)));

        $merged = $a2->merge($b);
        $this->assertSame([0, 1, 3, 4], array_map(static fn(Thing $t) => $t->value(), iterator_to_array($merged)));
    }

    public function testMapPreservesTypeAndOrder(): void
    {
        $set = SortedHashSet::empty(Thing::class)
            ->add(self::thing(2))
            ->add(self::thing(1))
            ->add(self::thing(3));

        // Iteration should be 1,2,3
        $this->assertSame([1, 2, 3], array_map(fn(Thing $t) => $t->value(), iterator_to_array($set)));

        // Map each element to value*2, still Thing type
        $mapped = $set->map(fn(Thing $t) => new Thing($t->value() * 2));

        // Order should follow original iteration order (mapped accordingly)
        $this->assertSame([2, 4, 6], array_map(fn(Thing $t) => $t->value(), iterator_to_array($mapped)));
    }

    public function testIsEmptyAndToArrayConversions(): void
    {
        $empty = SortedHashSet::empty(Thing::class);
        $this->assertTrue($empty->isEmpty());

        $set = $empty->add(self::thing(2))->add(self::thing(1));
        $this->assertFalse($set->isEmpty());

        // toArrayValue / toArray reflect sorted order of hashes/values
        $av = $set->toArrayValue();
        $arr = $av->toArray();
        $this->assertSame(['1', '2'], array_map('strval', array_keys($arr)));
        $this->assertSame([1, 2], array_map(fn(Thing $t) => $t->value(), array_values($arr)));

        $arr2 = $set->toArray();
        $this->assertSame(['1', '2'], array_map('strval', array_keys($arr2)));
    }
}

/**
 * Simple helper IHashableComparable implementation for testing SortedHashSet ordering.
 */
final class Thing implements IHashableComparable
{
    public function __construct(private int $v) {}

    public function value(): int { return $this->v; }

    public function compareTo(object $other): Compare
    {
        $ov = $other instanceof self ? $other->v : null;
        if ($ov === null) {
            // For safety in tests; not expected to happen
            return Compare::Equal;
        }
        if ($this->v === $ov) {
            return Compare::Equal;
        }
        return $this->v < $ov ? Compare::Less : Compare::Greater;
    }

    public function toHash(): string
    {
        return (string) $this->v;
    }
}
