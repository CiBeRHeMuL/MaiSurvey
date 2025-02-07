<?php

namespace App\Domain\Dto\Subject;

use App\Domain\Entity\Semester;

readonly class CreateSubjectDto
{
    public function __construct(
        private string $name,
        private Semester $semester,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSemester(): Semester
    {
        return $this->semester;
    }
}
