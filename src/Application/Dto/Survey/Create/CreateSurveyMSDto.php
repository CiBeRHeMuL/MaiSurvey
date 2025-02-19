<?php

namespace App\Application\Dto\Survey\Create;

use App\Application\Validator\Constraints as LAssert;
use Symfony\Component\Validator\Constraints as Assert;

readonly class CreateSurveyMSDto
{
    /**
     * @param string $title
     * @param array $subject_ids
     * @param string $actual_to
     * @param CreateItemDto[] $items
     */
    public function __construct(
        /** Заголовок */
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[Assert\NotBlank(message: 'Значение не должно быть пустым')]
        #[Assert\Length(max: 255, maxMessage: 'Значение должно быть короче 255 символов')]
        public string $title,
        /** ID предметов */
        #[Assert\Type('array', message: 'Значение должно быть строкой')]
        #[Assert\Count(min: 1, max: 20, minMessage: 'Необходимо указать хотя бы один предмет', maxMessage: 'Максимум 20 предметов')]
        #[Assert\All([
            new Assert\Type('string', message: 'Значение должно быть строкой'),
            new Assert\NotBlank(message: 'Значение не должно быть пустым'),
            new Assert\Uuid(message: 'Значение должно быть корректным uuid'),
        ])]
        public array $subject_ids,
        /** Актуален до */
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[Assert\NotBlank(message: 'Значение не должно быть пустым')]
        #[LAssert\DateTime]
        public string $actual_to,
        /** Вопросы */
        #[Assert\Type('array', message: 'Значение должно быть массивом')]
        public array $items,
    ) {
    }
}
