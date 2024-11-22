<?php

namespace App\Application\Dto\Auth;

use SensitiveParameter;
use Symfony\Component\Validator\Constraints as Assert;

readonly class SignInDto
{
    /**
     * @param string $email почта
     * @param string $password пароль
     */
    public function __construct(
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[Assert\Email(message: 'Неверный формат почты')]
        /** Почта */
        public string $email,
        #[SensitiveParameter]
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        /** Пароль */
        public string $password,
    ) {
    }
}
