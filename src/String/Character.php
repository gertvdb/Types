<?php

namespace Gertvdb\Types\String;

use InvalidArgumentException;
use Stringable;

final readonly class Character implements IString
{
    private StringValue $character;

    private function __construct(StringValue $character)
    {
        if (mb_strlen($character->__toString()) !== 1) {
            throw new InvalidArgumentException('Character must be a string of exactly 1 character.');
        }

        $this->character = $character;
    }

    public static function fromString(string|Stringable $value): self
    {
        return new self(StringValue::fromString($value));
    }

    public function toStringValue(): StringValue
    {
        return $this->character;
    }

    public function equals(Character $other): bool
    {
        return $this->character->equals($other->character);
    }

    public function toString(): string
    {
        return $this->__toString();
    }

    public function __toString(): string
    {
        return $this->character->__toString();
    }
}
