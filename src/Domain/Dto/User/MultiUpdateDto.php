<?php

namespace App\Domain\Dto\User;

readonly class MultiUpdateDto
{
    public function __construct(
        private string $file,
        private bool $headersInFirstRow,
        private string $emailCol,
        private string $lastNameCol,
        private string $firstNameCol,
        private string $patronymicCol,
        private string $groupNameCol,
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

    public function getLastNameCol(): string
    {
        return $this->lastNameCol;
    }

    public function getFirstNameCol(): string
    {
        return $this->firstNameCol;
    }

    public function getPatronymicCol(): string
    {
        return $this->patronymicCol;
    }

    public function getGroupNameCol(): string
    {
        return $this->groupNameCol;
    }
}
