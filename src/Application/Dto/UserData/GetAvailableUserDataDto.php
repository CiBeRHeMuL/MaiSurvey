<?php

namespace App\Application\Dto\UserData;

use App\Application\Validator\Constraints as LAssert;
use App\Domain\Enum\RoleEnum;
use App\Domain\Enum\SortTypeEnum;
use App\Domain\Service\UserData\UserDataService;
use Symfony\Component\Validator\Constraints as Assert;

readonly class GetAvailableUserDataDto
{
    /**
     * @param string|null $name имя для фильтрации
     * @param bool $with_group учитывать только данные с группой
     * @param string[]|null $group_ids группы для фильтрации
     * @param string $sort_by сортировка по
     * @param string $sort_type тип сортировки
     * @param int $offset
     * @param int|null $limit
     * @param string|null $for_role роль для которой фильтруем значения
     */
    public function __construct(
        /** Имя для фильтрации */
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[Assert\Length(min: 1, max: 255, minMessage: 'Имя должно быть длиннее 1 символа', maxMessage: 'Имя должно быть короче 255 символов')]
        #[Assert\NotBlank(message: 'Значение не должно быть пустым', allowNull: true)]
        public string|null $name = null,
        /** Учитывать только данные с группой */
        #[Assert\Type('boolean', message: 'Значение должно быть булевым значением')]
        public bool|null $with_group = null,
        /** Группы для фильтрации */
        #[Assert\All([
            new Assert\Type('string', message: 'Значение должно быть строкой'),
            new Assert\NotBlank(message: 'Значение не должно быть пустым'),
            new Assert\Uuid(message: 'Значение должно быть uuid'),
        ])]
        #[Assert\Count(max: 50, maxMessage: 'Поиск по более чем 50 значениям не поддерживается')]
        public array|null $group_ids = null,
        /** Сортировка по */
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[Assert\Choice(choices: UserDataService::GET_ALL_SORT, message: 'Значение должно входить в список допустимых')]
        #[Assert\NotBlank(message: 'Значение не должно быть пустым')]
        public string $sort_by = 'name',
        /** Тип сортировки */
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
        public int|null $limit = 20,
        /** Роль, для которой фильтруем значения */
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[LAssert\EnumChoice(enum: RoleEnum::class, message: 'Значение должно входить в список допустимых')]
        public string|null $for_role = null,
    ) {
    }
}
