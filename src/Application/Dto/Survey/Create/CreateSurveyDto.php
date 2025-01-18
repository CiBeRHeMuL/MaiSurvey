<?php

namespace App\Application\Dto\Survey\Create;

use App\Application\Validator\Constraints as LAssert;
use Symfony\Component\Validator\Constraints as Assert;

readonly class CreateSurveyDto
{
    /**
     * @param string $title
     * @param string $subject_id
     * @param string $actual_to
     * @param CreateItemDto[] $items
     */
    public function __construct(
        /** Заголовок */
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[Assert\Length(max: 255, maxMessage: 'Значение должно быть короче 255 символов')]
        public string $title,
        /** ID предмета */
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[Assert\Uuid(message: 'Значение должно быть корректным uuid')]
        public string $subject_id,
        /** Актуален до */
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[LAssert\DateTime]
        public string $actual_to,
        /** Вопросы */
        #[Assert\Type('array', message: 'Значение должно быть массивом')]
        public array $items,
    ) {
    }
}
