<?php

declare(strict_types=1);

namespace Gertvdb\Types\Array\Dictionary;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class ScalarDictionaryTest extends TestCase
{
    public function testEmptyAndAddGetHasAndRemoveWithIntKeys(): void
    {
        $d0 = ScalarDictionary::empty('int', 'string');
        $this->assertSame(0, $d0->count());
        $this->assertFalse($d0->has(1));

        $d1 = $d0->add(1, 'one')->add(2, 'two');
        $this->assertTrue($d1->has(1));
        $this->assertTrue($d1->has(2));
        $this->assertSame('one', $d1->get(1));
        $this->assertSame('two', $d1->get(2));

        // immutability
        $this->assertFalse($d0->has(1));

        $d2 = $d1->remove(1);
        $this->assertFalse($d2->has(1));
        $this->assertTrue($d2->has(2));
        $this->assertSame(1, $d2->count());
    }

    public function testFromArrayAndStringKeys(): void
    {
        $d = ScalarDictionary::fromArray(['a' => 10, 'b' => 20], 'string', 'int');
        $this->assertSame(2, $d->count());
        $this->assertTrue($d->has('a'));
        $this->assertTrue($d->has('b'));
        $this->assertSame(10, $d->get('a'));
        $this->assertSame(20, $d->get('b'));

        $this->assertSame(['a' => 10, 'b' => 20], $d->toArray());
    }

    public function testInvalidKeyTypeThrows(): void
    {
        $d = ScalarDictionary::empty('int', 'string');
        $this->expectException(InvalidArgumentException::class);
        $d->add('not-int', 'value');
    }

    public function testInvalidValueTypeThrows(): void
    {
        $d = ScalarDictionary::empty('string', 'int');
        $this->expectException(InvalidArgumentException::class);
        $d->add('key', 'not-int');
    }

    public function testGetOnMissingKeyThrows(): void
    {
        $d = ScalarDictionary::empty('string', 'int');
        $this->expectException(InvalidArgumentException::class);
        $d->get('missing');
    }

    public function testIterationYieldsKeyValuePairs(): void
    {
        $d = ScalarDictionary::fromArray(['x' => 1, 'y' => 2], 'string', 'int');
        $collected = [];
        foreach ($d as $k => $v) {
            $collected[$k] = $v;
        }
        $this->assertSame(['x' => 1, 'y' => 2], $collected);
    }
}
