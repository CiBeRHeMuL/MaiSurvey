<?php

namespace App\Domain\Dto\Subject;

readonly class ImportDto
{
    public function __construct(
        private string $file,
        private bool $headersInFirstRow,
        private string $nameCol,
        private string $yearCol,
        private string $semesterCol,
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

    public function getNameCol(): string
    {
        return $this->nameCol;
    }

    public function getSemesterCol(): string
    {
        return $this->semesterCol;
    }

    public function getYearCol(): string
    {
        return $this->yearCol;
    }

    public function isSkipIfExists(): bool
    {
        return $this->skipIfExists;
    }
}
