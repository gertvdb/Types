<?php

declare(strict_types=1);

namespace Gertvdb\Types\DateTime;

use Gertvdb\Types\DateTime\Formats\DayFormat;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class DayTest extends TestCase
{
    public function testFromIntAndFormatting(): void
    {
        $d = Day::fromInt(7);
        $this->assertSame(7, $d->toInt());
        $this->assertSame('7', $d->format(DayFormat::NUMERIC)->toString());
        $this->assertSame('07', $d->format(DayFormat::TWO_DIGIT)->toString());

        $d2 = Day::fromString('15');
        $this->assertTrue($d->isEqual(Day::fromInt(7)));
        $this->assertSame(15, $d2->toInt());
    }

    public function testBoundsAreEnforced(): void
    {
        $this->expectException(\OutOfRangeException::class);
        Day::fromInt(0);
    }

    public function testUpperBoundIsEnforced(): void
    {
        $this->expectException(\OutOfRangeException::class);
        Day::fromInt(32);
    }
}
