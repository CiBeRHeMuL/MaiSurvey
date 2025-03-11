<?php

namespace App\Application\Dto\Survey\Update;

use App\Application\Dto\Survey\Create\CreateItemDto;
use App\Application\Validator\Constraints as LAssert;
use App\Domain\Enum\SurveyStatusEnum;
use Symfony\Component\Validator\Constraints as Assert;

#[Assert\Cascade]
readonly class UpdateSurveyDto
{
    /**
     * @param string $title
     * @param string $subject_id
     * @param string|null $actual_to
     * @param UpdateItemDto[] $items
     * @param value-of<SurveyStatusEnum> $status
     */
    public function __construct(
        /** Заголовок */
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[Assert\NotBlank(message: 'Значение не должно быть пустым')]
        #[Assert\Length(max: 255, maxMessage: 'Значение должно быть короче 255 символов')]
        public string $title,
        /** ID предмета */
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[Assert\NotBlank(message: 'Значение не должно быть пустым')]
        #[Assert\Uuid(message: 'Значение должно быть корректным uuid')]
        public string $subject_id,
        /** Вопросы */
        #[Assert\Type('array', message: 'Значение должно быть массивом')]
        public array $items,
        /** Статус */
        #[LAssert\EnumChoice(SurveyStatusEnum::class)]
        public string $status,
        /** Актуален до */
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[LAssert\DateTime]
        public string|null $actual_to = null,
    ) {
    }
}
