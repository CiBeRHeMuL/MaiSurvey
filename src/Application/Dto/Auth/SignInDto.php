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
        /** Почта */
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[Assert\Email(message: 'Неверный формат почты')]
        #[Assert\NotBlank(message: 'Значение не должно быть пустым')]
        public string $email,
        /** Пароль */
        #[SensitiveParameter]
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[Assert\NotBlank(message: 'Значение не должно быть пустым')]
        public string $password,
    ) {
    }
}
