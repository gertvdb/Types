<?php

declare(strict_types=1);

namespace Gertvdb\Types\Array\HashSet;

use Gertvdb\Types\Int\IntValue;
use Gertvdb\Types\String\StringValue;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class HashSetTest extends TestCase
{
    public function testAddHasRemoveAndCount(): void
    {
        $set = HashSet::empty(IntValue::class);

        $one = IntValue::fromInt(1);
        $two = IntValue::fromInt(2);
        $twoDuplicate = IntValue::fromInt(2);

        $set2 = $set->add($one)->add($two)->add($twoDuplicate);

        // uniqueness by hash, duplicate should not increase count
        $this->assertSame(2, $set2->count());
        $this->assertTrue($set2->has($two));
        // has() also supports primitive probe via normalization
        $this->assertTrue($set2->has(2));
        $this->assertFalse($set2->has(IntValue::fromInt(3)));

        // immutability: original set unchanged
        $this->assertSame(0, $set->count());

        // remove existing
        $set3 = $set2->remove($two);
        $this->assertSame(1, $set3->count());
        $this->assertFalse($set3->has($two));

        // remove non-existing is a no-op, still immutable
        $set4 = $set3->remove(IntValue::fromInt(999));
        $this->assertSame(1, $set4->count());
    }

    public function testTypeEnforcementOnAdd(): void
    {
        $set = HashSet::empty(IntValue::class);

        // adding wrong type should throw
        $this->expectException(InvalidArgumentException::class);
        /** @phpstan-ignore-next-line intention: wrong type on purpose */
        $set->add(StringValue::fromString('x'));
    }

    public function testToArrayValueAndToArray(): void
    {
        $item1 = IntValue::fromInt(2);
        $item2 = IntValue::fromInt(1);

        $set = HashSet::empty(IntValue::class)
            ->add($item1)
            ->add($item2);

        $arrayValue = $set->toArrayValue();
        $arr = $arrayValue->toArray();

        // cast to string to avoid PHP's numeric-string keys being cast to ints
        $this->assertSame([
            $item1->toHash(),
            $item2->toHash()
        ], array_map('strval', array_keys($arr)));

        // values are IntValue instances
        $this->assertSame($item1, $arr['2']);
        $this->assertSame($item2, $arr['1']);
    }

    public function testIsEmpty(): void
    {
        $set = HashSet::empty(IntValue::class);
        $this->assertTrue($set->isEmpty());
        $set2 = $set->add(IntValue::fromInt(5));
        $this->assertFalse($set2->isEmpty());
    }
}
