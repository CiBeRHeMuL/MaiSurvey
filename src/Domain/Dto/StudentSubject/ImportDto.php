<?php

namespace App\Domain\Dto\StudentSubject;

use Symfony\Component\Uid\Uuid;

readonly class ImportDto
{
    /**
     * @param string $file
     * @param bool $headersInFirstRow
     * @param string $studentEmailCol
     * @param string $teacherEmailCol
     * @param string $subjectCol
     * @param string $typeCol
     * @param string $yearCol
     * @param string $semesterCol
     * @param bool $skipIfExists
     * @param Uuid|null $onlyForGroupId группа для которой надо импортировать данные (данные не для этой группы будут проигнорированы)
     */
    public function __construct(
        private string $file,
        private bool $headersInFirstRow,
        private string $studentEmailCol,
        private string $teacherEmailCol,
        private string $subjectCol,
        private string $typeCol,
        private string $yearCol,
        private string $semesterCol,
        private bool $skipIfExists,
        private Uuid|null $onlyForGroupId = null,
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

    public function getYearCol(): string
    {
        return $this->yearCol;
    }

    public function getSemesterCol(): string
    {
        return $this->semesterCol;
    }

    public function isSkipIfExists(): bool
    {
        return $this->skipIfExists;
    }

    public function getOnlyForGroupId(): ?Uuid
    {
        return $this->onlyForGroupId;
    }
}
