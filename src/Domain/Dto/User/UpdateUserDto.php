<?php

namespace App\Domain\Dto\User;

use App\Domain\Enum\RoleEnum;
use App\Domain\Enum\UserStatusEnum;

readonly class UpdateUserDto
{
    /**
     * @param RoleEnum[] $roles
     * @param UserStatusEnum $status
     * @param bool $needChangePassword
     */
    public function __construct(
        private array $roles,
        private UserStatusEnum $status,
        private bool $needChangePassword,
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

    public function isNeedChangePassword(): bool
    {
        return $this->needChangePassword;
    }
}
