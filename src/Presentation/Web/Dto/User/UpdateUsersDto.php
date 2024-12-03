<?php

namespace App\Presentation\Web\Dto\User;

use Symfony\Component\Validator\Constraints as Assert;

readonly class UpdateUsersDto
{
    public function __construct(
        /** Находятся ли в первой строке файла заголовки для столбцов */
        #[Assert\Type('boolean', message: 'Значение должно быть булевым значением')]
        public bool $headers_in_first_row = true,
        /** Столбец с почтой */
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[Assert\Regex('/^[A-Z]+$/u', message: 'Значение должно быть названием столбца excel')]
        public string $email_col = 'A',
        /** Столбец с фамилией */
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[Assert\Regex('/^[A-Z]+$/u', message: 'Значение должно быть названием столбца excel')]
        public string $last_name_col = 'B',
        /** Столбец с именем */
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[Assert\Regex('/^[A-Z]+$/u', message: 'Значение должно быть названием столбца excel')]
        public string $first_name_col = 'C',
        /** Столбец с отчеством */
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[Assert\Regex('/^[A-Z]+$/u', message: 'Значение должно быть названием столбца excel')]
        public string $patronymic_col = 'D',
        /** Столбец с названием группы */
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[Assert\Regex('/^[A-Z]+$/u', message: 'Значение должно быть названием столбца excel')]
        public string $group_name_col = 'E',
    ) {
    }
}
