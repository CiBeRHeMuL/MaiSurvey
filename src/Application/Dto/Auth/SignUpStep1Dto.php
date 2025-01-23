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
        /** Повторный пароль */
        #[SensitiveParameter]
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[Assert\NotBlank(message: 'Значение не должно быть пустым')]
        public string $repeat_password,
    ) {
    }
}
