<?php

namespace App\Domain\Service\Security;

use App\Domain\Exception\ValidationException;
use App\Domain\ValueObject\Email;

/**
 * Интерфейс сервиса для проверки корректности почты (например, на доменное имя).
 */
interface EmailCheckerServiceInterface
{
    /**
     * Если почта некорректная, то выбрасывается исключение.
     *
     * @param Email $email
     *
     * @return void
     * @throws ValidationException
     */
    public function checkEmail(Email $email): void;
}
