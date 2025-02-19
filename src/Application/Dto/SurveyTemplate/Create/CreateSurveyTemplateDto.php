<?php

namespace App\Application\Dto\SurveyTemplate\Create;

use Symfony\Component\Validator\Constraints as Assert;

readonly class CreateSurveyTemplateDto
{
    /**
     * @param string $name
     * @param string $title
     * @param CreateTemplateItemDto[] $items
     */
    public function __construct(
        /** Название шаблона */
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[Assert\NotBlank(message: 'Значение не должно быть пустым')]
        #[Assert\Length(max: 255, maxMessage: 'Значение должно быть короче 255 символов')]
        public string $name,
        /** Заголовок */
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[Assert\NotBlank(message: 'Значение не должно быть пустым')]
        #[Assert\Length(max: 255, maxMessage: 'Значение должно быть короче 255 символов')]
        public string $title,
        /** Вопросы */
        #[Assert\Type('array', message: 'Значение должно быть массивом')]
        public array $items,
    ) {
    }
}
