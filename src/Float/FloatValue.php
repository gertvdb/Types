<?php

declare(strict_types=1);

namespace Gertvdb\Types\Float;

use InvalidArgumentException;

/**
 * Represents an native float (between PHP_INT_MIN and PHP_INT_MAX).
 */
final readonly class FloatValue implements IFloat
{
    // PHP_FLOAT_MIN is the Minimum positive so we cant use that.
    public const float MIN = -PHP_FLOAT_MAX;
    public const float MAX = PHP_FLOAT_MAX;

    private float $value;

    public function __construct(float $value)
    {
        if ($value < self::MIN || $value > self::MAX) {
            throw new InvalidArgumentException(
                "Value must be between " . self::MIN . " and " . self::MAX . ", got {$value}"
            );
        }

        $this->value = $value;
    }

    public static function create(float $value): self
    {
        return new self($value);
    }

    /**
     * Useful to parse from user input (URL or forms)
     *
     * Input          Result
     * "0"            ✅ 0.0
     * "123"          ✅ 123.0
     * "-123"         ✅ -123.0
     * "0.0"          ✅ 0.0
     * "123.45"       ✅ 123.45
     * "-0.123"       ✅ -0.123
     * ".123"         ✅ 0.123
     * "0123"         ❌ throws exception
     * "00.1"         ❌ throws exception
     * " 123 "        ❌ throws exception
     * "12a3"         ❌ throws exception
     * "12.3.4"       ❌ throws exception
     */
    public static function fromString(string $input): self
    {
        // Regex explanation:
        // ^-?               → optional minus
        // (0|[1-9]\d*)      → zero OR non-zero integer part
        // (\.\d+)?          → optional fractional part
        // $                 → end of string
        if (!preg_match('/^-?(0|[1-9]\d*)(\.\d+)?$/', $input)) {
            throw new InvalidArgumentException("Invalid float format: {$input}");
        }

        // Safe cast: input has only digits and optional single dot
        $value = (float) $input;

        return new self($value);
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function toFloatValue(): FloatValue
    {
        return $this;
    }

    public function toFloat(): float
    {
       return $this->value;
    }
}
