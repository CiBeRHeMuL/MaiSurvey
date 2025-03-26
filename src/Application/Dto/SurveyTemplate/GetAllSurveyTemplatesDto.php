<?php

namespace App\Application\Dto\SurveyTemplate;

use App\Application\OpenApi\Attribute as LOA;
use App\Application\Validator\Constraints as LAssert;
use App\Domain\Enum\SortTypeEnum;
use App\Domain\Service\SurveyTemplate\SurveyTemplateService;
use Symfony\Component\Validator\Constraints as Assert;

readonly class GetAllSurveyTemplatesDto
{
    public function __construct(
        /** Название шаблона */
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        public string|null $name = null,
        /** Сортировка по */
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[Assert\Choice(choices: SurveyTemplateService::GET_ALL_SORT, message: 'Значение должно входить в список допустимых')]
        public string $sort_by = 'name',
        /** Тип сортировки */
        #[LOA\Enum(SortTypeEnum::class)]
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[LAssert\EnumChoice(enum: SortTypeEnum::class, message: 'Значение должно входить в список допустимых')]
        public string $sort_type = SortTypeEnum::Asc->value,
        #[Assert\Type('integer', message: 'Значение должно быть целым числом')]
        #[Assert\GreaterThanOrEqual(0, message: 'Значение должно быть больше или равно 0')]
        public int $offset = 0,
        #[Assert\Type('integer', message: 'Значение должно быть целым числом')]
        #[Assert\GreaterThanOrEqual(0, message: 'Значение должно быть больше или равно 0')]
        #[Assert\LessThanOrEqual(100, message: 'Значение должно быть меньше или равно 100')]
        public int|null $limit = 100,
    ) {
    }
}
