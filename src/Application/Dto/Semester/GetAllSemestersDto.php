<?php

namespace App\Application\Dto\Semester;

use App\Application\OpenApi\Attribute as LOA;
use App\Application\Validator\Constraints as LAssert;
use App\Domain\Enum\SortTypeEnum;
use App\Domain\Service\Semester\SemesterService;
use Symfony\Component\Validator\Constraints as Assert;

readonly class GetAllSemestersDto
{
    public function __construct(
        /** Сортировка по */
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[Assert\Choice(choices: SemesterService::GET_ALL_SORT, message: 'Значение должно входить в список допустимых')]
        #[Assert\NotBlank(message: 'Значение не должно быть пустым')]
        public string $sort_by = 'year',
        /** Тип сортировки */
        #[LOA\Enum(SortTypeEnum::class)]
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[LAssert\EnumChoice(enum: SortTypeEnum::class, message: 'Значение должно входить в список допустимых')]
        #[Assert\NotBlank(message: 'Значение не должно быть пустым')]
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
