<?php

namespace App\Domain\Service\Security;

use SensitiveParameter;

/**
 * Сервис для хеширования пароля.
 */
interface PasswordHasherServiceInterface
{
    public function hashPassword(#[SensitiveParameter] string $password): string;
}
