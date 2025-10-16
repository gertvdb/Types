<?php

declare(strict_types=1);

namespace Gertvdb\Types\DateTime;

use Gertvdb\Types\DateTime\Formats\HourFormat;
use Gertvdb\Types\Int\BoundedIntValue;
use Gertvdb\Types\Int\IInt;
use Gertvdb\Types\Int\IntRange;
use Gertvdb\Types\Int\IntValue;
use Gertvdb\Types\String\StringValue;

final readonly class Hour implements IInt
{
    public const int MIN = 0;
    public const int MAX = 23;

    public BoundedIntValue $value;

    private function __construct(int $int)
    {
        $range = IntRange::create(self::MIN, self::MAX);
        $this->value = BoundedIntValue::create($int, $range);
    }

    public static function fromInt(int $int): self
    {
        return new self($int);
    }

    /**
     * Useful to parse from user input (url or forms).
     */
    public static function fromString(string|\Stringable $string): self
    {
        $asInt = IntValue::fromString($string);
        return new self($asInt->toInt());
    }

    public function isEqual(self $other): bool
    {
        return $this->toInt() === $other->toInt();
    }

    public function format(HourFormat $format): StringValue
    {
        return match ($format) {
            HourFormat::NUMERIC => StringValue::fromInt($this->toInt()),
            HourFormat::TWO_DIGIT => StringValue::fromInt($this->toInt())->padLeft(2, '0'),
        };
    }

    public function toIntValue(): IntValue
    {
        return $this->value->toIntValue();
    }

    public function toInt(): int
    {
        return $this->value->toInt();
    }
}
