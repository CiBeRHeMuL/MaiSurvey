<?php

namespace App\Application\Dto\Me;

use Symfony\Component\Validator\Constraints as Assert;

readonly class UpdateMeDto
{
    public function __construct(
        /** Имя */
        #[Assert\Type('string')]
        #[Assert\Length(max: 255, maxMessage: 'Значение должно быть короче 255 символов')]
        public string $first_name,
        /** Фамилия */
        #[Assert\Type('string')]
        #[Assert\Length(max: 255, maxMessage: 'Значение должно быть короче 255 символов')]
        public string $last_name,
        /** Отчество */
        #[Assert\Type(['string', 'null'])]
        #[Assert\Length(max: 255, maxMessage: 'Значение должно быть короче 255 символов')]
        public string|null $patronymic,
        /** Удален */
        #[Assert\Type('boolean')]
        public bool $deleted,
    ) {
    }
}
