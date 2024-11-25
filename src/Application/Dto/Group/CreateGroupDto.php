<?php

namespace App\Application\Dto\Group;

use Symfony\Component\Validator\Constraints as Assert;

readonly class CreateGroupDto
{
    public function __construct(
        /** Имя группы */
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[Assert\Length(max: 255, maxMessage: 'Значение должно быть короче 255 символов')]
        public string $name,
    ) {
    }
}
