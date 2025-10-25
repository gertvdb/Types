<?php

declare(strict_types=1);

namespace Gertvdb\Types\Array\Dictionary;

use Gertvdb\Types\Array\Dictionary\Dictionary;
use Gertvdb\Types\Int\IntValue;
use Gertvdb\Types\String\StringValue;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class DictionaryTest extends TestCase
{
    public function testAddGetHasRemoveAndCount(): void
    {
        $dict = Dictionary::empty(IntValue::class, IntValue::class);

        $k1 = IntValue::fromInt(1);
        $v1 = IntValue::fromInt(10);
        $k2 = IntValue::fromInt(2);
        $v2 = IntValue::fromInt(20);

        $dict2 = $dict->add($k1, $v1)->add($k2, $v2);

        // count and immutability
        $this->assertSame(0, $dict->count());
        $this->assertSame(2, $dict2->count());

        // get and has using both object key and primitive probe
        $this->assertTrue($dict2->has($k1));
        $this->assertTrue($dict2->has(2));
        $this->assertFalse($dict2->has(IntValue::fromInt(999)));

        $this->assertSame($v1, $dict2->get($k1));
        $this->assertSame($v2, $dict2->get(2));

        // remove existing and non-existing
        $dict3 = $dict2->remove($k2);
        $this->assertSame(1, $dict3->count());
        $this->assertFalse($dict3->has($k2));

        $dict4 = $dict3->remove(IntValue::fromInt(12345));
        $this->assertSame(1, $dict4->count());
    }

    public function testTypeEnforcementOnAdd(): void
    {
        $dict = Dictionary::empty(StringValue::class, IntValue::class);

        $this->expectException(InvalidArgumentException::class);
        // wrong key type
        /** @phpstan-ignore-next-line intention: wrong type on purpose */
        $dict->add(IntValue::fromInt(1), IntValue::fromInt(2));
    }

    public function testToArrayValueAndToArray(): void
    {
        $k1 = IntValue::fromInt(1);
        $k2 = IntValue::fromInt(2);
        $v1 = IntValue::fromInt(100);
        $v2 = IntValue::fromInt(200);

        $dict = Dictionary::empty(IntValue::class, IntValue::class)
            ->add($k1, $v1)
            ->add($k2, $v2);

        $arrayValue = $dict->toArrayValue();
        $arr = $arrayValue->toArray();

        // keys are hashes (strings of the int values)
        $this->assertSame(['1', '2'], array_map('strval', array_keys($arr)));
        $this->assertSame($k1, $arr['1']['key']);
        $this->assertSame($v1, $arr['1']['value']);
        $this->assertSame($k2, $arr['2']['key']);
        $this->assertSame($v2, $arr['2']['value']);

        // toArray proxies the same structure
        $arr2 = $dict->toArray();
        $this->assertSame($arr, $arr2);
    }

    public function testIsEmpty(): void
    {
        $dict = Dictionary::empty(IntValue::class, IntValue::class);
        $this->assertTrue($dict->isEmpty());
        $dict2 = $dict->add(IntValue::fromInt(1), IntValue::fromInt(1));
        $this->assertFalse($dict2->isEmpty());
    }
}
