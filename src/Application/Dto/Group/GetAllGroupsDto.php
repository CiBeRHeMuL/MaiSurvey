<?php

namespace App\Application\Dto\Group;

use App\Domain\Enum\SortTypeEnum;
use App\Domain\Service\Group\GroupService;
use Symfony\Component\Validator\Constraints as Assert;
use App\Application\Validator\Constraints as LAssert;

readonly class GetAllGroupsDto
{
    /**
     * @param string|null $name имя для фильтрации
     * @param string $sort_by сортировка по
     * @param string $sort_type тип сортировки
     * @param int $offset
     * @param int|null $limit
     */
    public function __construct(
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[Assert\Length(min: 1, max: 255, minMessage: 'Имя должно быть длиннее 1 символа', maxMessage: 'Имя должно быть короче 255 символов')]
        /** Имя для фильтрации */
        public string|null $name = null,
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[Assert\Choice(choices: GroupService::GET_ALL_SORT, message: 'Значение должно входить в список допустимых')]
        /** Сортировка по */
        public string $sort_by = 'name',
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[LAssert\EnumChoice(enum: SortTypeEnum::class, message: 'Значение должно входить в список допустимых')]
        /** Тип сортировки */
        public string $sort_type = SortTypeEnum::Asc->value,
        #[Assert\Type('integer', message: 'Значение должно быть целым числом')]
        #[Assert\GreaterThanOrEqual(0, message: 'Значение должно быть больше или равно 0')]
        public int $offset = 0,
        #[Assert\Type('integer', message: 'Значение должно быть целым числом')]
        #[Assert\GreaterThanOrEqual(0, message: 'Значение должно быть больше или равно 0')]
        #[Assert\LessThanOrEqual(100, message: 'Значение должно быть меньше или равно 100')]
        public int|null $limit = 20,
    ) {
    }
}
