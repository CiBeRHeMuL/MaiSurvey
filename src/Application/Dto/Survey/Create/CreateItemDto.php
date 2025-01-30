<?php

namespace App\Application\Dto\Survey\Create;

use App\Application\OpenApi\Attribute as LOA;
use App\Application\Validator\Constraints as LAssert;
use App\Domain\Enum\SurveyItemTypeEnum;
use App\Domain\Enum\TeacherSubjectTypeEnum;
use Symfony\Component\Validator\Constraints as Assert;

readonly class CreateItemDto
{
    public function __construct(
        /** Обязательность ответа */
        #[Assert\Type('boolean', message: 'Значение должно быть булевым')]
        public bool $answer_required,
        /** Тип вопроса */
        #[LOA\Enum(SurveyItemTypeEnum::class)]
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[Assert\NotBlank(message: 'Значение не должно быть пустым')]
        #[LAssert\EnumChoice(SurveyItemTypeEnum::class)]
        public string $type,
        /** Текст вопроса */
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[Assert\NotBlank(message: 'Значение не должно быть пустым')]
        public string $text,
        /** Позиция */
        #[Assert\Type('integer', message: 'Значение должно быть целым числом')]
        public int $position,
        /** Данные вопроса */
        public ItemDataInterface $data,
        /** Тип предмета */
        #[LOA\Enum(TeacherSubjectTypeEnum::class)]
        #[Assert\Type(['string', 'null'], message: 'Значение должно быть строкой')]
        #[LAssert\EnumChoice(TeacherSubjectTypeEnum::class)]
        public string|null $subject_type,
    ) {
    }
}
