<?php

namespace App\Application\Dto\Survey\Create;

use App\Application\Validator\Constraints as LAssert;
use App\Domain\Enum\SurveyStatusEnum;
use Symfony\Component\Validator\Constraints as Assert;

readonly class CreateMSFromTemplateDto
{
    public function __construct(
        /** ID шаблона */
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[Assert\NotBlank(message: 'Значение не должно быть пустым')]
        #[Assert\Uuid(message: 'Значение должно быть корректным uuid')]
        public string $template_id,
        /** ID предметов */
        #[Assert\Type('array', message: 'Значение должно быть строкой')]
        #[Assert\Count(min: 1, max: 20, minMessage: 'Необходимо указать хотя бы один предмет', maxMessage: 'Максимум 20 предметов')]
        #[Assert\All([
            new Assert\Type('string', message: 'Значение должно быть строкой'),
            new Assert\NotBlank(message: 'Значение не должно быть пустым'),
            new Assert\Uuid(message: 'Значение должно быть корректным uuid'),
        ])]
        public array $subject_ids,
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
