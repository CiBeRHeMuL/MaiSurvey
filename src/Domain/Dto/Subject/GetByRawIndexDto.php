<?php

namespace App\Domain\Dto\Subject;

use App\Domain\Dto\Semester\GetSemesterByIndexDto;

readonly class GetByRawIndexDto
{
    public function __construct(
        private string $name,
        private GetSemesterByIndexDto $semesterDto,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSemesterDto(): GetSemesterByIndexDto
    {
        return $this->semesterDto;
    }
}
