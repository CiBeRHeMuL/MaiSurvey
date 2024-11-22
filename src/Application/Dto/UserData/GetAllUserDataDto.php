<?php

namespace App\Application\Dto\UserData;

use App\Domain\Enum\SortTypeEnum;
use App\Domain\Service\UserData\UserDataService;
use Symfony\Component\Validator\Constraints as Assert;

readonly class GetAllUserDataDto
{
    /**
     * @param string|null $name
     * @param bool $only_with_group
     * @param string[]|null $group_ids
     * @param string $sort_by
     * @param string $sort_type
     * @param int $offset
     * @param int|null $limit
     */
    public function __construct(
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[Assert\Length(min: 1, max: 255, minMessage: 'Имя должно быть длиннее 1 символа', maxMessage: 'Имя должно быть короче 255 символов')]
        public string|null $name = null,
        #[Assert\Type('boolean', message: 'Значение должно быть булевым значением')]
        public bool|null $only_with_group = false,
        #[Assert\All([
            new Assert\Type('string', message: 'Значение должно быть строкой'),
            new Assert\Uuid(message: 'Значение должно быть uuid'),
        ])]
        #[Assert\Count(max: 50, maxMessage: 'Поиск по более чем 50 значениям не поддерживается')]
        public array|null $group_ids = null,
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[Assert\Choice(choices: UserDataService::GET_ALL_SORT, message: 'Значение должно входить в список допустимых')]
        public string $sort_by = 'name',
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[Assert\Choice(choices: [SortTypeEnum::Asc->value, SortTypeEnum::Desc->value], message: 'Значение должно входить в список допустимых')]
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
