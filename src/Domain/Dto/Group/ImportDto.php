<?php

namespace App\Domain\Dto\Group;

readonly class ImportDto
{
    public function __construct(
        private string $file,
        private bool $headersInFirstRow,
        private string $nameCol,
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
}
