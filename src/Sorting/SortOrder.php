<?php

declare(strict_types=1);

namespace Gertvdb\Types\Sorting;

use InvalidArgumentException;

final readonly class SortOrder
{
    private function __construct(private int $value)
    {
        if (!in_array($value, [-1, 0, 1], true)) {
            throw new InvalidArgumentException("SortOrder value must be -1, 0, or 1");
        }
    }

    public static function less(): self
    {
        return new self(-1);
    }

    public static function equal(): self
    {
        return new self(0);
    }

    public static function greater(): self
    {
        return new self(1);
    }

    public static function fromComparison(int $result): self
    {
        return new self($result <=> 0);
    }

    public function value(): int
    {
        return $this->value;
    }

    public function reverse(): self
    {
        return new self(-$this->value);
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
