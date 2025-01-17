<?php

namespace App\Domain\Dto\StudentSubject;

readonly class ImportByGroupsDto
{
    public function __construct(
        private string $file,
        private bool $headersInFirstRow,
        private string $groupNameCol,
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

    public function getGroupNameCol(): string
    {
        return $this->groupNameCol;
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
