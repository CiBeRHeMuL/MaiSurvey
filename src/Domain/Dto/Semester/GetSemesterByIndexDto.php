<?php

namespace App\Domain\Dto\Semester;

readonly class GetSemesterByIndexDto
{
    public function __construct(
        private int $year,
        private bool $spring,
    ) {
    }

    public function getYear(): int
    {
        return $this->year;
    }

    public function isSpring(): bool
    {
        return $this->spring;
    }
}
