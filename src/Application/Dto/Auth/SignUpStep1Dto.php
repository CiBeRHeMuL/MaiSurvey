<?php

namespace App\Application\Dto\Auth;

use SensitiveParameter;
use Symfony\Component\Validator\Constraints as Assert;

readonly class SignUpStep1Dto
{
    public function __construct(
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[Assert\Email(message: 'Неверный формат почты')]
        public string $email,
        #[SensitiveParameter]
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        public string $password,
        #[SensitiveParameter]
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        public string $repeat_password,
    ) {
    }
}
