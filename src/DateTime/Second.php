<?php

declare(strict_types=1);

namespace Gertvdb\Types\DateTime;

use Gertvdb\Types\DateTime\Formats\SecondFormat;
use Gertvdb\Types\Int\BoundedIntValue;
use Gertvdb\Types\Int\IInt;
use Gertvdb\Types\Int\IntRange;
use Gertvdb\Types\Int\IntValue;
use Gertvdb\Types\String\StringValue;

final readonly class Second implements IInt
{
    public const int MIN = 0;
    public const int MAX = 59;

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
        return $this->value->toInt() === $other->value->toInt();
    }

    public function format(SecondFormat $format): StringValue
    {
        return match ($format) {
            SecondFormat::NUMERIC => StringValue::fromInt($this->toInt()),
            SecondFormat::TWO_DIGIT => StringValue::fromInt($this->toInt())->padLeft(2, '0'),
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
