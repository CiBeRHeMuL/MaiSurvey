<?php

namespace App\Domain\Service\Security;

use SensitiveParameter;

/**
 * Сервис для проверки пароля.
 */
interface PasswordVerificationServiceInterface
{
    public function verifyPassword(#[SensitiveParameter] string $password, #[SensitiveParameter] string $hash): bool;
}
