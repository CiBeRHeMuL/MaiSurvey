<?php

namespace App\Presentation\Web\Dto\TeacherSubject;

use Symfony\Component\Validator\Constraints as Assert;

readonly class ImportTeacherSubjectsDto
{
    public function __construct(
        /** Находятся ли в первой строке файла заголовки для столбцов */
        #[Assert\Type('boolean', message: 'Значение должно быть булевым значением')]
        public bool $headers_in_first_row = true,
        /** Столбец с предметом */
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[Assert\Regex('/^[A-Z]+$/u', message: 'Значение должно быть названием столбца excel')]
        #[Assert\NotBlank(message: 'Значение не должно быть пустым')]
        public string $subject_col = 'A',
        /** Столбец с почтой */
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[Assert\Regex('/^[A-Z]+$/u', message: 'Значение должно быть названием столбца excel')]
        #[Assert\NotBlank(message: 'Значение не должно быть пустым')]
        public string $email_col = 'B',
        /** Столбец с типом предмета */
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[Assert\Regex('/^[A-Z]+$/u', message: 'Значение должно быть названием столбца excel')]
        #[Assert\NotBlank(message: 'Значение не должно быть пустым')]
        public string $type_col = 'C',
        /** Столбец с годом */
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[Assert\Regex('/^[A-Z]+$/u', message: 'Значение должно быть названием столбца excel')]
        #[Assert\NotBlank(message: 'Значение не должно быть пустым')]
        public string $year_col = 'D',
        /** Столбец с семестром */
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[Assert\Regex('/^[A-Z]+$/u', message: 'Значение должно быть названием столбца excel')]
        #[Assert\NotBlank(message: 'Значение не должно быть пустым')]
        public string $semester_col = 'E',
        /** Пропускать новые предметы, которые конфликтуют с существующими */
        #[Assert\Type('boolean', message: 'Значение должно быть булевым значением')]
        public bool $skip_if_exists = true,
    ) {
    }
}
