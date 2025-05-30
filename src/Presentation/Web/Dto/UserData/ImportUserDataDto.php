<?php

namespace App\Presentation\Web\Dto\UserData;

use App\Application\Validator\Constraints as LAssert;
use App\Domain\Enum\RoleEnum;
use Symfony\Component\Validator\Constraints as Assert;

readonly class ImportUserDataDto
{
    public function __construct(
        /** Роль */
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[LAssert\EnumChoice(enum: RoleEnum::class, message: 'Значение должно входить в список допустимых')]
        public string $for_role,
        /** Находятся ли в первой строке файла заголовки для столбцов */
        #[Assert\Type('boolean', message: 'Значение должно быть булевым значением')]
        public bool $headers_in_first_row = true,
        /** Столбец с фамилией */
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[Assert\Regex('/^[A-Z]+$/u', message: 'Значение должно быть названием столбца excel')]
        #[Assert\NotBlank(message: 'Значение не должно быть пустым')]
        public string $last_name_col = 'A',
        /** Столбец с именем */
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[Assert\Regex('/^[A-Z]+$/u', message: 'Значение должно быть названием столбца excel')]
        #[Assert\NotBlank(message: 'Значение не должно быть пустым')]
        public string $first_name_col = 'B',
        /** Столбец с отчеством */
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[Assert\Regex('/^[A-Z]+$/u', message: 'Значение должно быть названием столбца excel')]
        #[Assert\NotBlank(message: 'Значение не должно быть пустым')]
        public string $patronymic_col = 'C',
        /** Столбец с названием группы */
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[Assert\Regex('/^[A-Z]+$/u', message: 'Значение должно быть названием столбца excel')]
        #[Assert\NotBlank(message: 'Значение не должно быть пустым')]
        public string $group_name_col = 'D',
    ) {
    }
}
