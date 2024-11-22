<?php

namespace App\Domain\Dto\Auth;

use App\Domain\ValueObject\Email;
use SensitiveParameter;

readonly class SignInDto
{
    public function __construct(
        private Email $email,
        #[SensitiveParameter]
        private string $password,
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
}
