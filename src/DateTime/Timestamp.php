<?php

declare(strict_types=1);

namespace Gertvdb\Types\DateTime;

use Gertvdb\Types\Int\BoundedIntValue;
use Gertvdb\Types\Int\IInt;
use Gertvdb\Types\Int\IntRange;
use Gertvdb\Types\Int\IntValue;
use DateTimeImmutable;
use DateTimeZone;

final readonly class Timestamp implements IDateTime, IInt
{
    public const int MIN = PHP_INT_MIN;
    public const int MAX = PHP_INT_MAX;

    public Timezone $timezone;
    public BoundedIntValue $value;

    private function __construct(int $int)
    {
        $range = IntRange::create(self::MIN, self::MAX);
        $this->value = BoundedIntValue::create($int, $range);
        $this->timezone = Timezone::ETC_UTC;
    }

    public static function fromInt(int $int): self {
        return new self($int);
    }

    public function toDateTimeValue(): DateTime
    {
        return DateTime::fromTimestamp($this);
    }

    public function toDateTimeImmutable(): DateTimeImmutable
    {
        $dt = DateTimeImmutable::createFromFormat('U', (string) $this->value->toInt(), new DateTimeZone('UTC'));
        if ($dt === false) {
            throw new \RuntimeException('Failed to create DateTimeImmutable from timestamp');
        }
        return $dt;
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
