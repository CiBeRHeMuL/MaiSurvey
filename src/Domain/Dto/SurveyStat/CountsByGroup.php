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
}
