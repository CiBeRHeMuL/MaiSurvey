<?php

namespace App\Infrastructure\Service\Security;

use App\Domain\Enum\ValidationErrorSlugEnum;
use App\Domain\Exception\ValidationException;
use App\Domain\Service\Security\PasswordCheckerServiceInterface;
use App\Domain\Validation\ValidationError;
use SensitiveParameter;

class PasswordCheckerService implements PasswordCheckerServiceInterface
{
    /**
     * @inheritDoc
     */
    public function checkPasswordStrength(#[SensitiveParameter] string $password): void
    {
        // Пароль должен содержать:
        // 1. Хотя юы одну строчную латинскую букву
        // 2. Хотя бы одну прописную латинскую букву
        // 3. Хотя бы один спецсимвол ~!@#$%^&*()_-+={[}];:/?|\\,.><
        // 4. Хотя бы одну цифру
        // 5. Иметь длину не менее 11 символов

        if (strlen($password) < 11) {
            throw ValidationException::new([
                new ValidationError(
                    'password',
                    ValidationErrorSlugEnum::WrongField->getSlug(),
                    'Пароль должен быть длиннее 10 символов',
                ),
            ]);
        }

        if (!preg_match('/[a-z]/', $password)) {
            throw ValidationException::new([
                new ValidationError(
                    'password',
                    ValidationErrorSlugEnum::WrongField->getSlug(),
                    'Пароль должен содержать хотя бы одну строчную латинскую букву',
                ),
            ]);
        }

        if (!preg_match('/[A-Z]/', $password)) {
            throw ValidationException::new([
                new ValidationError(
                    'password',
                    ValidationErrorSlugEnum::WrongField->getSlug(),
                    'Пароль должен содержать хотя бы одну прописную латинскую букву',
                ),
            ]);
        }

        if (!preg_match('/[0-9]/', $password)) {
            throw ValidationException::new([
                new ValidationError(
                    'password',
                    ValidationErrorSlugEnum::WrongField->getSlug(),
                    'Пароль должен содержать хотя бы одну цифру',
                ),
            ]);
        }

        if (!preg_match('/[~!@#$%^&*()_\-+={\[}\];:\/?|\\\\,.><]/', $password)) {
            throw ValidationException::new([
                new ValidationError(
                    'password',
                    ValidationErrorSlugEnum::WrongField->getSlug(),
                    'Пароль должен содержать хотя бы один спецсимвол ~!@#$%^&*()_-+={[}];:/?|\\,.><',
                ),
            ]);
        }
    }
}
