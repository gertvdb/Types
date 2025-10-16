<?php

declare(strict_types=1);

namespace Gertvdb\Types\String;

use Assert\InvalidArgumentException;
use Stringable;

final class StringValue implements IString
{
    /**
     * The list of characters that are considered "invisible" in strings.
     *
     * @var string
     */
    private const string INVISIBLE_CHARACTERS = '\x{0009}\x{0020}\x{00A0}\x{00AD}\x{034F}\x{061C}\x{115F}\x{1160}\x{17B4}\x{17B5}\x{180E}\x{2000}\x{2001}\x{2002}\x{2003}\x{2004}\x{2005}\x{2006}\x{2007}\x{2008}\x{2009}\x{200A}\x{200B}\x{200C}\x{200D}\x{200E}\x{200F}\x{202F}\x{205F}\x{2060}\x{2061}\x{2062}\x{2063}\x{2064}\x{2065}\x{206A}\x{206B}\x{206C}\x{206D}\x{206E}\x{206F}\x{3000}\x{2800}\x{3164}\x{FEFF}\x{FFA0}\x{1D159}\x{1D173}\x{1D174}\x{1D175}\x{1D176}\x{1D177}\x{1D178}\x{1D179}\x{1D17A}\x{E0020}';

    private readonly string $value;

    private function __construct(string|Stringable $value)
    {
        $casted = (string)$value;

        // The list of characters that are considered "invisible" in strings.
        $trimDefaultCharacters = " \n\r\t\v\0";
        $normalized = preg_replace('~^[\s' . self::INVISIBLE_CHARACTERS . $trimDefaultCharacters . ']+|[\s' . self::INVISIBLE_CHARACTERS . $trimDefaultCharacters . ']+$~u', '', $casted) ?? \trim($casted);

        if ($normalized === null) {
            throw new InvalidArgumentException('Something went wrong while normalizing.', 0);
        }

        if ($normalized === '') {
            throw new InvalidArgumentException('String value cannot be empty.', 0);
        }

        $this->value = $normalized;
    }


    public static function fromString(string|Stringable $value): self
    {
        return new self($value);
    }

    public static function fromInt(int $value): self
    {
        $casted = (string)$value;
        return new self($casted);
    }

    public function equals(self $other, bool $strict = TRUE): bool
    {
        if ($strict) {
            return $this->value === $other->value;
        }

        return $this->lowercase() === $other->lowercase();
    }

    public function isNumeric(): bool
    {
        return is_numeric($this->value);
    }

    public function lowercase(): self
    {
        return new self(mb_strtolower($this->value, 'UTF-8'));
    }

    public function uppercase(): self
    {
        return new self(mb_strtoupper($this->value, 'UTF-8'));
    }

    public function contains(Stringable|string $string): bool
    {
        $casted = (string)$string;
        return str_contains($this->value, $casted);
    }

    public function length(): int
    {
        return mb_strlen($this->value, 'UTF-8');
    }

    public function before(Stringable|string $string): self
    {
        $casted = (string)$string;

        $pos = strpos($this->value, $casted);
        if (!$pos) {
            throw new InvalidArgumentException(sprintf('String does not contain "%s".', $casted), 0);
        }

        return new self(mb_substr($this->value, 0, $pos));
    }

    public function after(Stringable|string $string): self
    {
        $casted = (string)$string;

        $pos = strpos($this->value, $casted);
        if (!$pos) {
            throw new InvalidArgumentException(sprintf('String does not contain "%s".', $casted), 0);
        }

        return new self(mb_substr($this->value, $pos + strlen($casted), null, 'UTF-8'));
    }

    public function prepend(Stringable|string $string, Stringable|string $seperator = ''): self
    {
        $casted = (string)$string;
        $castedSeperator = (string)$seperator;
        return new self($casted . $castedSeperator . $this->value);
    }

    public function append(Stringable|string $string, Stringable|string $seperator = ''): self
    {
        $casted = (string)$string;
        $castedSeperator = (string)$seperator;
        return new self($this->value . $castedSeperator . $casted);
    }

    public function mask(Character $character, int $index, ?int $length = null): self
    {
        $segment = mb_substr($this->value, $index, $length, 'UTF-8');

        // When no segment to mask, we return the stringValue unmasked.
        if ($segment === '') {
            return $this;
        }

        $strlen = mb_strlen($this->value, 'UTF-8');
        $startIndex = $index;

        if ($index < 0) {
            $startIndex = $index < -$strlen ? 0 : $strlen + $index;
        }

        $start = mb_substr($this->value, 0, $startIndex, 'UTF-8');
        $segmentLen = mb_strlen($segment, 'UTF-8');
        $end = mb_substr($this->value, $startIndex + $segmentLen);

        $masked = $start . str_repeat(mb_substr((string)$character, 0, 1, 'UTF-8'), $segmentLen) . $end;
        return new self($masked);
    }

    public function replace(Stringable|string $replace, Stringable|string $by): self
    {
        $replaced = str_replace((string)$replace, (string)$by, $this->value);
        return new self($replaced);
    }

    public function startsWith(Stringable|string $needle): bool
    {
        $casted = (string)$needle;
        if ($casted === '') {
            return false;
        }
        return str_starts_with($this->value, $casted);
    }

    public function endsWith(Stringable|string $needle): bool
    {
        $casted = (string)$needle;
        if ($casted === '') {
            return false;
        }
        return str_ends_with($this->value, $casted);
    }

    public function trimLeft(Stringable|string $trimString = " \n\r\t\v\0") : self
    {
        $newValue = ltrim($this->value, $trimString);
        return new self($newValue);
    }

    public function trimRight(Stringable|string $trimString = " \n\r\t\v\0") : self
    {
        $newValue = rtrim($this->value, $trimString);
        return new self($newValue);
    }

    public function padLeft(int $length, Stringable|string $padString): self
    {
        $padded = str_pad($this->value, $length, $padString, STR_PAD_LEFT);
        return new self($padded);
    }

    public function padRight(int $length, Stringable|string $padString): self
    {
        $padded = str_pad($this->value, $length, $padString, STR_PAD_RIGHT);
        return new self($padded);
    }

    public function substr(int $start, ?int $length = null): self
    {
        if ($length === null) {
            return new self(mb_substr($this->value, $start));
        }

        return new self(mb_substr($this->value, $start, $length));
    }

    public function toStringValue(): StringValue
    {
        return $this;
    }

    public function toString(): string
    {
        return $this->__toString();
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
