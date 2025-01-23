<?php

namespace App\Application\Dto\Subject;

use Symfony\Component\Validator\Constraints as Assert;

readonly class CreateSubjectDto
{
    public function __construct(
        /** Название предмета */
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[Assert\Length(max: 255, maxMessage: 'Значение должно быть короче 255 символов')]
        #[Assert\NotBlank(message: 'Значение не должно быть пустым')]
        public string $name,
    ) {
    }
}
