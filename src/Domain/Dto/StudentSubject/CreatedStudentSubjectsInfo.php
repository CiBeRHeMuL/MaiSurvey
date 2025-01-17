<?php

namespace App\Domain\Dto\StudentSubject;

readonly class CreatedStudentSubjectsInfo
{
    public function __construct(
        private int $created,
        private int $skipped,
    ) {
    }

    public function getCreated(): int
    {
        return $this->created;
    }

    public function getSkipped(): int
    {
        return $this->skipped;
    }
}
