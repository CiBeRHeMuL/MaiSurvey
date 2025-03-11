<?php

namespace App\Application\Dto\Survey\Create;

use App\Application\Validator\Constraints as LAssert;
use App\Domain\Enum\SurveyStatusEnum;
use Symfony\Component\Validator\Constraints as Assert;

readonly class CreateFromTemplateDto
{
    public function __construct(
        /** ID шаблона */
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[Assert\NotBlank(message: 'Значение не должно быть пустым')]
        #[Assert\Uuid(message: 'Значение должно быть корректным uuid')]
        public string $template_id,
        /** ID предмета */
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[Assert\NotBlank(message: 'Значение не должно быть пустым')]
        #[Assert\Uuid(message: 'Значение должно быть корректным uuid')]
        public string $subject_id,
        /** Статус */
        #[LAssert\EnumChoice(SurveyStatusEnum::class)]
        public string $status,
        /** Время закрытия */
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[LAssert\DateTime]
        public string|null $actual_to = null,
    ) {
    }
}
