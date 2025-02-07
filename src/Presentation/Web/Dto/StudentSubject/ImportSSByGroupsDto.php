<?php

namespace App\Presentation\Web\Dto\StudentSubject;

use Symfony\Component\Validator\Constraints as Assert;

readonly class ImportSSByGroupsDto
{
    public function __construct(
        /** Находятся ли в первой строке файла заголовки для столбцов */
        #[Assert\Type('boolean', message: 'Значение должно быть булевым значением')]
        public bool $headers_in_first_row = true,
        /** Столбец с названием группы */
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[Assert\Regex('/^[A-Z]+$/u', message: 'Значение должно быть названием столбца excel')]
        #[Assert\NotBlank(message: 'Значение не должно быть пустым')]
        public string $group_name_col = 'A',
        /** Столбец с почтой преподавателя */
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[Assert\Regex('/^[A-Z]+$/u', message: 'Значение должно быть названием столбца excel')]
        #[Assert\NotBlank(message: 'Значение не должно быть пустым')]
        public string $teacher_email_col = 'B',
        /** Столбец с названием предмета */
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[Assert\Regex('/^[A-Z]+$/u', message: 'Значение должно быть названием столбца excel')]
        #[Assert\NotBlank(message: 'Значение не должно быть пустым')]
        public string $subject_col = 'C',
        /** Столбец с типом предмета */
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[Assert\Regex('/^[A-Z]+$/u', message: 'Значение должно быть названием столбца excel')]
        #[Assert\NotBlank(message: 'Значение не должно быть пустым')]
        public string $type_col = 'D',
        /** Столбец с годом */
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[Assert\Regex('/^[A-Z]+$/u', message: 'Значение должно быть названием столбца excel')]
        #[Assert\NotBlank(message: 'Значение не должно быть пустым')]
        public string $year_col = 'E',
        /** Столбец с номером семестра (1 - весенний, 2 - осенний) */
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[Assert\Regex('/^[A-Z]+$/u', message: 'Значение должно быть названием столбца excel')]
        #[Assert\NotBlank(message: 'Значение не должно быть пустым')]
        public string $semester_col = 'F',
        /** Пропускать новые предметы, которые конфликтуют с существующими */
        #[Assert\Type('boolean', message: 'Значение должно быть булевым значением')]
        public bool $skip_if_exists = false,
    ) {
    }
}
