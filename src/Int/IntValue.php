<?php

declare(strict_types=1);

namespace Gertvdb\Types\Int;

use Gertvdb\Types\Array\IHashable;
use Gertvdb\Types\String\IString;
use Gertvdb\Types\String\StringValue;
use InvalidArgumentException;

/**
 * Represents an native integer (between PHP_INT_MIN and PHP_INT_MAX).
 */
final readonly class IntValue implements IInt, IString, IHashable
{
    public const int MIN = PHP_INT_MIN;
    public const int MAX = PHP_INT_MAX;

    private int $value;

    public function __construct(int $value)
    {
        if ($value < self::MIN || $value > self::MAX) {
            throw new InvalidArgumentException(
                "Value must be between " . self::MIN . " and " . self::MAX . ", got {$value}"
            );
        }

        $this->value = $value;
    }

    public static function fromInt(int $input): self
    {
        return new self($input);
    }

    /**
     * Useful to parse from user input (url or forms).
     *
     *   Input      Result
     *   "0"        ✅ 0
     *   "123"      ✅ 123
     *   "-123"     ✅ -123
     *   "0123"     ✅ 123
     *   "00"       ✅ 0
     *   " 123 "    ✅ 123
     *   "12a3"     ❌ throws exception
     *   "12.3"     ❌ throws exception
     */
    public static function fromString(string|\Stringable $input): self
    {
        $normalize = StringValue::fromString($input)->trimLeft();

        /**
         * Regex explanation:
         *
         *  ^-?     → optional minus sign at the start
         *  \d+     → one or more digits (allows leading zeros)
         *  $       → end of string
         *
         */
        if (!preg_match('/^-?\d+$/', $normalize->toString())) {
            throw new InvalidArgumentException("Invalid integer format: {$normalize->toString()}");
        }

        // safe cast, string has only digits
        $value = (int) $normalize->toString();
        return new self($value);
    }

    public function length(): int
    {
        return StringValue::fromInt($this->value)->length();
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function toStringValue(): StringValue
    {
        return StringValue::fromString($this);
    }

    public function toInt(): int
    {
        return $this->value;
    }

    public function toIntValue(): IntValue
    {
        return $this;
    }

    public function toString(): string
    {
        return $this->__toString();
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }

    public function toHash(): string
    {
        return $this->__toString();
    }
}
