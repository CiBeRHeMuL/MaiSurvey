<?php

namespace App\Application\Dto\Survey\Create;

use Symfony\Component\Validator\Constraints as Assert;

readonly class Choice
{
    public function __construct(
        /** Описание */
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[Assert\NotBlank(message: 'Значение не должно быть пустым')]
        public string $text,
        /** Значение */
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[Assert\NotBlank(message: 'Значение не должно быть пустым')]
        public string $value,
    ) {
    }
}
