<?php

namespace App\Application\Dto\Auth;

use SensitiveParameter;
use Symfony\Component\Validator\Constraints as Assert;

readonly class SignUpStep1Dto
{
    /**
     * @param string $email почта
     * @param string $password пароль
     * @param string $repeat_password повторный пароль
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
        #[SensitiveParameter]
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        /** Повторный пароль */
        public string $repeat_password,
    ) {
    }
}
