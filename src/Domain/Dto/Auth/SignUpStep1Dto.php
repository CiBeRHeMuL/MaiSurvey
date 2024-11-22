<?php

namespace App\Domain\Dto\Auth;

use App\Domain\Enum\RoleEnum;
use App\Domain\ValueObject\Email;
use SensitiveParameter;

readonly class SignUpStep1Dto
{
    public function __construct(
        private Email $email,
        #[SensitiveParameter]
        private string $password,
        #[SensitiveParameter]
        private string $repeatPassword,
        private RoleEnum $role,
    ) {
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getRepeatPassword(): string
    {
        return $this->repeatPassword;
    }

    public function getRole(): RoleEnum
    {
        return $this->role;
    }
}
