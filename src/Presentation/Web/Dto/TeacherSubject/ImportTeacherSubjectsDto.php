<?php

namespace App\Presentation\Web\Dto\TeacherSubject;

use Symfony\Component\Validator\Constraints as Assert;

readonly class ImportTeacherSubjectsDto
{
    public function __construct(
        /** Находятся ли в первой строке файла заголовки для столбцов */
        #[Assert\Type('boolean', message: 'Значение должно быть булевым значением')]
        public bool $headers_in_first_row = true,
        /** Столбец с фамилией */
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[Assert\Regex('/^[A-Z]+$/u', message: 'Значение должно быть названием столбца excel')]
        public string $subject_col = 'A',
        /** Столбец с именем */
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[Assert\Regex('/^[A-Z]+$/u', message: 'Значение должно быть названием столбца excel')]
        public string $email_col = 'B',
        /** Столбец с отчеством */
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[Assert\Regex('/^[A-Z]+$/u', message: 'Значение должно быть названием столбца excel')]
        public string $type_col = 'C',
    ) {
    }
}
