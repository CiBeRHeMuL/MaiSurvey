<?php

namespace App\Domain\Dto\User;

use App\Domain\Enum\RoleEnum;
use App\Domain\Enum\UserStatusEnum;
use App\Domain\ValueObject\Email;
use SensitiveParameter;

readonly class CreateUserDto
{
    public function __construct(
        private Email $email,
        private UserStatusEnum $status,
        private RoleEnum $role,
        #[SensitiveParameter]
        private string $password,
    ) {
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function getStatus(): UserStatusEnum
    {
        return $this->status;
    }

    public function getRole(): RoleEnum
    {
        return $this->role;
    }

    public function getPassword(): string
    {
        return $this->password;
    }
}
