<?php

namespace App\Infrastructure\Service\Security;

use App\Domain\Service\Security\PasswordHasherServiceInterface;
use SensitiveParameter;

class PasswordHasherService implements PasswordHasherServiceInterface
{
    public function hashPassword(#[SensitiveParameter] string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }
}
