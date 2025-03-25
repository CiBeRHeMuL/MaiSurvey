<?php

namespace App\Domain\Dto\User;

use App\Domain\Enum\RoleEnum;
use App\Domain\Enum\UserStatusEnum;

readonly class UpdateUserDto
{
    /**
     * @param RoleEnum[] $roles
     * @param UserStatusEnum $status
     * @param bool $deleted
     */
    public function __construct(
        private array $roles,
        private UserStatusEnum $status,
        private bool $deleted,
    ) {
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function getStatus(): UserStatusEnum
    {
        return $this->status;
    }

    public function isDeleted(): bool
    {
        return $this->deleted;
    }
}
