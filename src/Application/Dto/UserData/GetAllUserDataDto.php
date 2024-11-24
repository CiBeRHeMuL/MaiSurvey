<?php

namespace App\Application\Dto\UserData;

use App\Domain\Enum\RoleEnum;
use App\Domain\Enum\SortTypeEnum;
use App\Domain\Service\UserData\UserDataService;
use Symfony\Component\Validator\Constraints as Assert;
use App\Application\Validator\Constraints as LAssert;

readonly class GetAllUserDataDto
{
    /**
     * @param string|null $name имя для фильтрации
     * @param bool $with_group учитывать только данные с группой
     * @param bool|null $with_user учитывать только данные с пользователем
     * @param string[]|null $group_ids группы для фильтрации
     * @param string $sort_by сортировка по
     * @param string $sort_type тип сортировки
     * @param int $offset
     * @param int|null $limit
     * @param string|null $for_role роль для которой фильтруем значения
     */
    public function __construct(
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[Assert\Length(min: 1, max: 255, minMessage: 'Имя должно быть длиннее 1 символа', maxMessage: 'Имя должно быть короче 255 символов')]
        /** Имя для фильтрации */
        public string|null $name = null,
        #[Assert\Type('boolean', message: 'Значение должно быть булевым значением')]
        /** Учитывать только данные с группой */
        public bool|null $with_group = null,
        #[Assert\Type('boolean', message: 'Значение должно быть булевым значением')]
        /** Учитывать только данные с пользователем */
        public bool|null $with_user = null,
        #[Assert\All([
            new Assert\Type('string', message: 'Значение должно быть строкой'),
            new Assert\Uuid(message: 'Значение должно быть uuid'),
        ])]
        #[Assert\Count(max: 50, maxMessage: 'Поиск по более чем 50 значениям не поддерживается')]
        /** Группы для фильтрации */
        public array|null $group_ids = null,
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[Assert\Choice(choices: UserDataService::GET_ALL_SORT, message: 'Значение должно входить в список допустимых')]
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
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[LAssert\EnumChoice(enum: RoleEnum::class, message: 'Значение должно входить в список допустимых')]
        public string|null $for_role = null,
    ) {
    }
}
