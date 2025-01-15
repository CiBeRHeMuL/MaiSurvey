<?php

namespace App\Domain\Dto\User;

use App\Domain\Enum\RoleEnum;
use SensitiveParameter;

readonly class ImportDto
{
    public function __construct(
        private string $file,
        private RoleEnum $forRole,
        private bool $headersInFirstRow,
        private string $lastNameCol,
        private string $firstNameCol,
        private string $patronymicCol,
        private string $groupNameCol,
        #[SensitiveParameter]
        private string $password,
    ) {
    }

    public function getFile(): string
    {
        return $this->file;
    }

    public function getForRole(): RoleEnum
    {
        return $this->forRole;
    }

    public function isHeadersInFirstRow(): bool
    {
        return $this->headersInFirstRow;
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

    public function getPassword(): string
    {
        return $this->password;
    }
}
