<?php

namespace App\Infrastructure\Service\Security;

use App\Domain\Service\Security\PasswordVerificationServiceInterface;
use SensitiveParameter;

class PasswordVerificationService implements PasswordVerificationServiceInterface
{
    public function verifyPassword(#[SensitiveParameter] string $password, #[SensitiveParameter] string $hash): bool
    {
        return password_verify($password, $hash);
    }
}
