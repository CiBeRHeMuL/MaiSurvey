<?php

namespace App\Application\Dto\Auth;

use Symfony\Component\Validator\Constraints as Assert;

readonly class SignUpStep2Dto
{
    /**
     * @param string $user_data_id id данных пользователя
     */
    public function __construct(
        /** ID данных пользователя */
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[Assert\Uuid(message: 'Значение должно быть корректным uuid')]
        public string $user_data_id,
    ) {
    }
}
