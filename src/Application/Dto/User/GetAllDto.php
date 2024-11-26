<?php

namespace App\Application\Dto\User;

use App\Application\Validator\Constraints as LAssert;
use App\Domain\Enum\RoleEnum;
use App\Domain\Enum\SortTypeEnum;
use App\Domain\Enum\UserStatusEnum;
use App\Domain\Service\User\UserService;
use Symfony\Component\Validator\Constraints as Assert;

readonly class GetAllDto
{
    public function __construct(
        /** Роли для фильтрации */
        #[Assert\All([
            new Assert\Type('string', message: 'Значение должно быть строкой'),
            new LAssert\EnumChoice(enum: RoleEnum::class, message: 'Значение должно входить в список допустимых'),
        ])]
        #[Assert\Count(max: 50, maxMessage: 'Поиск по более чем 50 значениям не поддерживается')]
        public array|null $roles = null,
        /** Имя для фильтрации */
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[Assert\Length(min: 1, max: 255, minMessage: 'Имя должно быть длиннее 1 символа', maxMessage: 'Имя должно быть короче 255 символов')]
        public string|null $name = null,
        /** Почта для фильтрации */
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[Assert\Length(min: 1, max: 255, minMessage: 'Почта должна быть длиннее 1 символа', maxMessage: 'Почта должна быть короче 255 символов')]
        public string|null $email = null,
        /** Фильтр по метке удаления */
        #[Assert\Type('boolean', message: 'Значение должно быть булевым значением')]
        public bool|null $deleted = null,
        /** Статус для фильтрации */
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[LAssert\EnumChoice(enum: UserStatusEnum::class, message: 'Значение должно входить в список допустимых')]
        public string|null $status = null,
        /** Группы для фильтрации */
        #[Assert\All([
            new Assert\Type('string', message: 'Значение должно быть строкой'),
            new Assert\Uuid(message: 'Значение должно быть uuid'),
        ])]
        #[Assert\Count(max: 50, maxMessage: 'Поиск по более чем 50 значениям не поддерживается')]
        public array|null $group_ids = null,
        /** Фильтровать только пользователей с группой */
        #[Assert\Type('boolean', message: 'Значение должно быть булевым значением')]
        public bool|null $with_group = null,
        /** Создан после */
        #[LAssert\DateTime]
        public string|null $created_from = null,
        /** Создан до */
        #[LAssert\DateTime]
        public string|null $created_to = null,
        /** Сортировка по */
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[Assert\Choice(choices: UserService::GET_ALL_SORT, message: 'Значение должно входить в список допустимых')]
        public string $sort_by = 'name',
        /** Тип сортировки */
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
