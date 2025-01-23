<?php

namespace App\Application\Dto\StudentSubject;

use App\Application\Validator\Constraints as LAssert;
use App\Domain\Enum\SortTypeEnum;
use App\Domain\Service\StudentSubject\StudentSubjectService;
use Symfony\Component\Validator\Constraints as Assert;

readonly class GetMyStudentSubjectsDto
{
    public function __construct(
        /** Актуален ли предмет на текущий момент */
        #[Assert\Type('boolean', message: 'Значение должно быть булевым')]
        public bool|null $actual = null,
        /** Предметы для фильтрации */
        #[Assert\All([
            new Assert\Type('string', message: 'Значение должно быть строкой'),
            new Assert\Uuid(message: 'Значение должно быть uuid'),
            new Assert\NotBlank(message: 'Значение не должно быть пустым'),
        ])]
        #[Assert\Count(max: 50, maxMessage: 'Поиск по более чем 50 значениям не поддерживается')]
        public array|null $subject_ids = null,
        /** Преподаватели для фильтрации */
        #[Assert\All([
            new Assert\Type('string', message: 'Значение должно быть строкой'),
            new Assert\Uuid(message: 'Значение должно быть uuid'),
            new Assert\NotBlank(message: 'Значение не должно быть пустым'),
        ])]
        #[Assert\Count(max: 50, maxMessage: 'Поиск по более чем 50 значениям не поддерживается')]
        public array|null $teacher_ids = null,
        /** Сортировка по */
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[Assert\Choice(choices: StudentSubjectService::GET_ALL_SORT, message: 'Значение должно входить в список допустимых')]
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
        public int|null $limit = 100,
    ) {
    }
}
