<?php

namespace App\Application\Dto\Survey;

use App\Application\Validator\Constraints as LAssert;
use App\Domain\Enum\SortTypeEnum;
use App\Domain\Service\Survey\SurveyService;
use Symfony\Component\Validator\Constraints as Assert;

readonly class GetMySurveysDto
{
    public function __construct(
        /** Предметы для фильтрации */
        #[Assert\All([
            new Assert\Type('string', message: 'Значение должно быть строкой'),
            new Assert\Uuid(message: 'Значение должно быть uuid'),
            new Assert\NotBlank(message: 'Значение не должно быть пустым'),
        ])]
        #[Assert\Count(max: 50, maxMessage: 'Поиск по более чем 50 значениям не поддерживается')]
        public array|null $subject_ids = null,
        /** Завершен ли опрос */
        #[Assert\Type('boolean', message: 'Значение должно быть булевым')]
        public bool|null $completed = null,
        /** Сортировка по */
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[Assert\Choice(choices: SurveyService::GET_MY_SORT, message: 'Значение должно входить в список допустимых')]
        public string $sort_by = 'name',
        /** Тип сортировки */
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[LAssert\EnumChoice(enum: SortTypeEnum::class, message: 'Значение должно входить в список допустимых')]
        public string $sort_type = SortTypeEnum::Desc->value,
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
