<?php

declare(strict_types=1);

namespace Gertvdb\Types\DateTime;

use InvalidArgumentException;

readonly class TimeSchedule {

    /** @var TimeSlot[] */
    private array $slots;

    private function __construct(array $slots)
    {
        $this->slots = $slots;
    }

    public static function empty(): self
    {
        return new self([]);
    }

    /**
     * Add a new TimeSlot to the schedule
     * Throws InvalidTimeSlot if it overlaps with existing slots
     */
    public function addSlot(TimeSlot $slot): self
    {
        foreach ($this->slots as $existingSlot) {
            if ($existingSlot->overlaps($slot)) {
                throw new InvalidArgumentException("TimeSlot overlaps with an existing slot.");
            }
        }

        // Add and sort by start time
        $newSlots = $this->slots;
        $newSlots[] = $slot;
        usort($newSlots, static fn(TimeSlot $a, TimeSlot $b) =>
            $a->start->toNanoseconds() <=> $b->start->toNanoseconds()
        );

        return new self($newSlots);
    }

    public function slots(): array
    {
        return $this->slots;
    }

}
