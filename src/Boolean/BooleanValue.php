<?php

declare(strict_types=1);

namespace Gertvdb\Types\Boolean;

use Gertvdb\Types\Int\IntValue;
use Gertvdb\Types\String\StringValue;
use InvalidArgumentException;

/**
 * Represents an native bool.
 */
final readonly class BooleanValue implements IBoolean
{
    private bool $value;

    public function __construct(bool $value)
    {
        $this->value = $value;
    }

    public static function fromBoolean(bool $value): self
    {
        return new self($value);
    }

    /**
     * Useful to parse from user input (url or forms).
     *
     *   Input      Result
     *   "false"    ✅ false
     *   "true"     ✅ true
     *   "1"        ✅ true
     *   "0"        ✅ false
     *   "00"       ❌ throws exception
     *   " 123 "    ❌ throws exception
     *   "12a3"     ❌ throws exception
     *   "12.3"     ❌ throws exception
     */
    public static function fromString(string $input): self
    {
        $normalize = StringValue::fromString($input)->lowercase();
        if (!in_array($normalize->toString(), ['true', 'false', '1', '0'], true)) {
            throw new InvalidArgumentException("Invalid boolean format: {$input}");
        }

        $trueOrFalse = $normalize->toString() === 'true' || $normalize->toString() === '1';
        return new self($trueOrFalse);
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function toInt(): int
    {
        return $this->value ? 1 : 0;
    }

    public function toIntValue(): IntValue
    {
        return IntValue::fromInt($this->toInt());
    }

    public function toString(): string
    {
        return $this->__toString();
    }

    public function __toString(): string {
        return $this->value ? 'true' : 'false';
    }

    public function toStringValue(): StringValue
    {
        return StringValue::fromString($this->__toString());
    }

    public function toBoolValue(): BooleanValue
    {
        return $this;
    }

    public function toBool(): bool
    {
       return $this->value;
    }
}
