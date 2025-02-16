<?php

namespace App\Domain\Dto\TeacherSubject;

readonly class ImportDto
{
    public function __construct(
        private string $file,
        private bool $headersInFirstRow,
        private string $emailCol,
        private string $subjectCol,
        private string $typeCol,
        private string $yearCol,
        private string $semesterCol,
    ) {
    }

    public function getFile(): string
    {
        return $this->file;
    }

    public function isHeadersInFirstRow(): bool
    {
        return $this->headersInFirstRow;
    }

    public function getEmailCol(): string
    {
        return $this->emailCol;
    }

    public function getSubjectCol(): string
    {
        return $this->subjectCol;
    }

    public function getTypeCol(): string
    {
        return $this->typeCol;
    }

    public function getSemesterCol(): string
    {
        return $this->semesterCol;
    }

    public function getYearCol(): string
    {
        return $this->yearCol;
    }
}
