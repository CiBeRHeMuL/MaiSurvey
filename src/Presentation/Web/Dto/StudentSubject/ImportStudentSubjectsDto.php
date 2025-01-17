<?php

namespace App\Presentation\Web\Dto\StudentSubject;

use Symfony\Component\Validator\Constraints as Assert;

readonly class ImportStudentSubjectsDto
{
    public function __construct(
        /** Находятся ли в первой строке файла заголовки для столбцов */
        #[Assert\Type('boolean', message: 'Значение должно быть булевым значением')]
        public bool $headers_in_first_row = true,
        /** Столбец с почтой студента */
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[Assert\Regex('/^[A-Z]+$/u', message: 'Значение должно быть названием столбца excel')]
        public string $student_email_col = 'A',
        /** Столбец с почтой преподавателя */
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[Assert\Regex('/^[A-Z]+$/u', message: 'Значение должно быть названием столбца excel')]
        public string $teacher_email_col = 'B',
        /** Столбец с названием предмета */
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[Assert\Regex('/^[A-Z]+$/u', message: 'Значение должно быть названием столбца excel')]
        public string $subject_col = 'C',
        /** Столбец с типом предмета */
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[Assert\Regex('/^[A-Z]+$/u', message: 'Значение должно быть названием столбца excel')]
        public string $type_col = 'D',
        /** Столбец с датой начала актуальности предмета */
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[Assert\Regex('/^[A-Z]+$/u', message: 'Значение должно быть названием столбца excel')]
        public string $actual_from_col = 'E',
        /** Столбец с датой окончания актуальности предмета */
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[Assert\Regex('/^[A-Z]+$/u', message: 'Значение должно быть названием столбца excel')]
        public string $actual_to_col = 'F',
        /** Пропускать новые предметы, которые конфликтуют с существующими */
        #[Assert\Type('boolean', message: 'Значение должно быть булевым значением')]
        public bool $skip_if_exists = false,
    ) {
    }
}
