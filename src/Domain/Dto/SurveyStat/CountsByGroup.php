<?php

namespace App\Domain\Dto\SurveyStat;

readonly class CountsByGroup
{
    public function __construct(
        public string $name,
        public int $available_count,
        public int $completed_count,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAvailableCount(): int
    {
        return $this->available_count;
    }

    public function getCompletedCount(): int
    {
        return $this->completed_count;
    }
}
