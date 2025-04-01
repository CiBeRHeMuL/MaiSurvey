<?php

namespace App\Application\Dto\Auth;

use SensitiveParameter;
use Symfony\Component\Validator\Constraints as Assert;

readonly class ChangePasswordDto
{
    public function __construct(
        /** Старый пароль */
        #[SensitiveParameter]
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[Assert\NotBlank(message: 'Значение не должно быть пустым')]
        public string $old_password,
        /** Новый пароль */
        #[SensitiveParameter]
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[Assert\NotBlank(message: 'Значение не должно быть пустым')]
        public string $new_password,
        /** Новый пароль еще раз */
        #[SensitiveParameter]
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[Assert\NotBlank(message: 'Значение не должно быть пустым')]
        public string $repeat_password,
    ) {
    }
}
