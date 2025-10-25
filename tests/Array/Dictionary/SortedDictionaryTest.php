<?php

declare(strict_types=1);

namespace Gertvdb\Types\Array\Dictionary;

use Gertvdb\Types\Array\Dictionary\SortedDictionary;
use Gertvdb\Types\Array\IHashableComparable;
use Gertvdb\Types\Order\Compare;
use Gertvdb\Types\Int\IntValue;
use PHPUnit\Framework\TestCase;

final class SortedDictionaryTest extends TestCase
{
    private static function kv(int $k, int $v): array
    {
        return [IntValue::fromInt($k), new Thing($v)];
    }

    public function testDefaultSortingByCompareToOnValues(): void
    {
        $d = SortedDictionary::empty(IntValue::class, Thing::class);

        [$k1, $v1] = self::kv(1, 30);
        [$k2, $v2] = self::kv(2, 10);
        [$k3, $v3] = self::kv(3, 20);

        $d2 = $d->add($k1, $v1)->add($k2, $v2)->add($k3, $v3);

        $iter = [];
        foreach ($d2 as $k => $v) {
            $iter[] = [$k->toInt(), $v->value()];
        }

        // Values should be iterated in ascending Thing::value order
        $this->assertSame([[2, 10], [3, 20], [1, 30]], $iter);

        // get() with primitive key normalization
        $this->assertSame(10, $d2->get(2)->value());

        // count and isEmpty
        $this->assertSame(3, $d2->count());
        $this->assertFalse($d2->isEmpty());
    }

    public function testCustomComparatorReverseOrder(): void
    {
        // Reverse comparator on values
        $desc = static fn(IHashableComparable $a, IHashableComparable $b): int => $b->compareTo($a)->value;
        $d = SortedDictionary::empty(IntValue::class, Thing::class, $desc);

        $item1 = new Thing(1);
        $item2 = new Thing(3);
        $item3 = new Thing(2);

        $d = $d->add(1, $item1)
            ->add(2, $item2)
            ->add(3, $item3);

        $iterVals = array_map(static fn(Thing $t) => $t->value(), $d->toArray());
        $this->assertSame([3, 2, 1], array_values($iterVals));
    }

    public function testRemoveAndToArrayValueOrder(): void
    {
        $item1 = new Thing(2);
        $item2 = new Thing(1);
        $item3 = new Thing(3);

        $d = SortedDictionary::empty(IntValue::class, Thing::class)
            ->add(1, $item1)
            ->add(2, $item2)
            ->add(3, $item3);

        // Remove middle value (2)
        $d2 = $d->remove(1);

        // Remaining values should be 1,3
        $arr = $d2->toArrayValue()->toArray();
        $this->assertSame(['2', '3'], array_map('strval', array_keys($arr)));

        $iterVals = array_map(static fn(Thing $t) => $t->value(), $arr);
        $this->assertSame([1, 3], array_values($iterVals));
    }
}

final class Thing implements IHashableComparable
{
    public function __construct(private int $v) {}
    public function value(): int { return $this->v; }
    public function compareTo(object $other): Compare
    {
        $ov = $other instanceof self ? $other->v : null;
        if ($ov === null) { return Compare::Equal; }
        if ($this->v === $ov) { return Compare::Equal; }
        return $this->v < $ov ? Compare::Less : Compare::Greater;
    }
    public function toHash(): string { return (string) $this->v; }
}
