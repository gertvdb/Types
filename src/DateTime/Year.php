<?php

declare(strict_types=1);

namespace Gertvdb\Types\DateTime;

use Gertvdb\Types\DateTime\Formats\YearFormat;
use Gertvdb\Types\Int\BoundedIntValue;
use Gertvdb\Types\Int\IInt;
use Gertvdb\Types\Int\IntRange;
use Gertvdb\Types\Int\IntValue;
use Gertvdb\Types\String\StringValue;

final readonly class Year implements IInt
{
    public const int MIN = -999999999;
    public const int MAX = 999999999;

    public BoundedIntValue $value;

    private function __construct(int $int)
    {
        if ($int === 0) {
            throw new \InvalidArgumentException("Year 0 does not exist.");
        }

        $range = IntRange::create(self::MIN, self::MAX);
        $this->value = BoundedIntValue::create($int, $range);
    }

    public static function fromInt(int $value): self
    {
        return new self($value);
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

    public function format(YearFormat $format): StringValue
    {
        return match ($format) {
            YearFormat::NUMERIC => StringValue::fromInt($this->toInt()),
            YearFormat::TWO_DIGIT => StringValue::fromInt($this->toInt())->padLeft(2, '0'),
            YearFormat::ROMAN => (function () {
                $num = abs($this->toInt());
                $map = [
                    'M' => 1000,
                    'CM' => 900,
                    'D' => 500,
                    'CD' => 400,
                    'C' => 100,
                    'XC' => 90,
                    'L' => 50,
                    'XL' => 40,
                    'X' => 10,
                    'IX' => 9,
                    'V' => 5,
                    'IV' => 4,
                    'I' => 1,
                ];

                $rawRoman = '';
                foreach ($map as $symbol => $value) {
                    while ($num >= $value) {
                        $rawRoman .= $symbol;
                        $num -= $value;
                    }
                }

                // Prefix with minus for BCE/negative years
                $roman = $this->toInt() < 0 ? '-' . $rawRoman : $rawRoman;
                return StringValue::fromString($roman);
            })(),
        };
    }

    public function toInt(): int
    {
        return $this->value->toInt();
    }

    public function toIntValue(): IntValue
    {
        return $this->value->toIntValue();
    }

    public function firstDayOfYear(): DateOnly
    {
        return DateOnly::from($this->value->toInt(), 1, 1);
    }

    public function lastDayOfYear(): DateOnly
    {
        return DateOnly::from($this->value->toInt(), 12, 31);
    }
}
