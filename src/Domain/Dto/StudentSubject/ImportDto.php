<?php

namespace App\Domain\Dto\StudentSubject;

readonly class ImportDto
{
    public function __construct(
        private string $file,
        private bool $headersInFirstRow,
        private string $studentEmailCol,
        private string $teacherEmailCol,
        private string $subjectCol,
        private string $typeCol,
        private string $actualFromCol,
        private string $actualToCol,
        private bool $skipIfExists,
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

    public function getStudentEmailCol(): string
    {
        return $this->studentEmailCol;
    }

    public function getTeacherEmailCol(): string
    {
        return $this->teacherEmailCol;
    }

    public function getSubjectCol(): string
    {
        return $this->subjectCol;
    }

    public function getTypeCol(): string
    {
        return $this->typeCol;
    }

    public function getActualFromCol(): string
    {
        return $this->actualFromCol;
    }

    public function getActualToCol(): string
    {
        return $this->actualToCol;
    }

    public function isSkipIfExists(): bool
    {
        return $this->skipIfExists;
    }
}
