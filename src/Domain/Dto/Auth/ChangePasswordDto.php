<?php

namespace App\Domain\Dto\Auth;

readonly class ChangePasswordDto
{
    public function __construct(
        private string $oldPassword,
        private string $newPassword,
        private string $repeatPassword,
    ) {
    }

    public function getOldPassword(): string
    {
        return $this->oldPassword;
    }

    public function getNewPassword(): string
    {
        return $this->newPassword;
    }

    public function getRepeatPassword(): string
    {
        return $this->repeatPassword;
    }
}
