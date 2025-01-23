<?php

namespace App\Presentation\Web\Dto\Subject;

use Symfony\Component\Validator\Constraints as Assert;

readonly class ImportSubjectsDto
{
    public function __construct(
        /** Находятся ли в первой строке файла заголовки для столбцов */
        #[Assert\Type('boolean', message: 'Значение должно быть булевым значением')]
        public bool $headers_in_first_row = true,
        /** Столбец с названием */
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[Assert\Regex('/^[A-Z]+$/u', message: 'Значение должно быть названием столбца excel')]
        #[Assert\NotBlank(message: 'Значение не должно быть пустым')]
        public string $name_col = 'A',
    ) {
    }
}
