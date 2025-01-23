<?php

namespace App\Presentation\Web\Dto\Auth;

use Symfony\Component\Validator\Constraints as Assert;

readonly class RefreshCredentialsDto
{
    public function __construct(
        /** Токен для обновления */
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[Assert\NotBlank(message: 'Значение не должно быть пустым')]
        public string $refresh_token,
    ) {
    }
}
