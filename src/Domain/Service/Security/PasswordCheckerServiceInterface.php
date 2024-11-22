<?php

namespace App\Domain\Service\Security;

use App\Domain\Exception\ValidationException;
use SensitiveParameter;

/** Сервис для проверки пароля. */
interface PasswordCheckerServiceInterface
{
    /**
     * @param string $password
     *
     * @return void
     * @throws ValidationException
     */
    public function checkPasswordStrength(#[SensitiveParameter] string $password): void;
}
