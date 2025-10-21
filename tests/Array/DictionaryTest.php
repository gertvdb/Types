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
    public function testAddGetHasRemoveAndIteration(): void
    {
        $dict = Dictionary::empty(StringValue::class, 'int');

        $k1 = StringValue::fromString('alpha');
        $k2 = StringValue::fromString('beta');

        $dict2 = $dict->add($k1, 10)->add($k2, 20);
        $this->assertSame(2, $dict2->count());

        $this->assertTrue($dict2->has($k1));
        $this->assertTrue($dict2->has($k2));
        $this->assertSame(10, $dict2->get($k1));
        $this->assertSame(20, $dict2->get($k2));

        $iterated = [];
        foreach ($dict2 as $key => $value) {
            $this->assertInstanceOf(StringValue::class, $key);
            $iterated[] = [$key->toHash(), $value];
        }
        $this->assertSame([
            [$k1->toHash(), 10],
            [$k2->toHash(), 20],
        ], $iterated);

        $dict3 = $dict2->remove($k1);
        $this->assertSame(1, $dict3->count());
        $this->assertFalse($dict3->has($k1));
        $this->assertTrue($dict3->has($k2));
    }

    public function testToArrayUsesHashesAndValues(): void
    {
        $dict = Dictionary::empty(StringValue::class, 'int');
        $k1 = StringValue::fromString('one');
        $k2 = StringValue::fromString('two');
        $dict = $dict->add($k1, 1)->add($k2, 2);

        $arr = $dict->toArray();
        $this->assertSame([ $k1->toHash(), $k2->toHash() ], array_keys($arr));
        $this->assertSame([1, 2], array_values($arr));
    }

    public function testTypeValidationForKeyAndValue(): void
    {
        $dict = Dictionary::empty(StringValue::class, 'int');
        $this->expectException(InvalidArgumentException::class);
        // Wrong key type: IntValue is IHashable but not StringValue
        $dict->add(IntValue::fromInt(1), 1);
    }

    public function testInvalidValueTypeThrows(): void
    {
        $dict = Dictionary::empty(StringValue::class, 'int');
        $this->expectException(InvalidArgumentException::class);
        $dict->add(StringValue::fromString('k'), 'not-an-int');
    }

    public function testGetNonExistingThrows(): void
    {
        $dict = Dictionary::empty(StringValue::class, 'int');
        $this->expectException(InvalidArgumentException::class);
        $dict->get(StringValue::fromString('missing'));
    }

    public function testRemoveNonExistingNoOp(): void
    {
        $dict = Dictionary::empty(StringValue::class, 'int');
        $dict2 = $dict->remove(StringValue::fromString('nope'));
        $this->assertSame(0, $dict2->count());
    }
}
