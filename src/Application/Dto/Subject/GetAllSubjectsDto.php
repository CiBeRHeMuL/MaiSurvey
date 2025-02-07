<?php

namespace App\Application\Dto\Subject;

use App\Application\Validator\Constraints as LAssert;
use App\Domain\Enum\SortTypeEnum;
use App\Domain\Service\Subject\SubjectService;
use Symfony\Component\Validator\Constraints as Assert;

readonly class GetAllSubjectsDto
{
    /**
     * @param string[]|null $semester_ids
     * @param string|null $name название для фильтрации
     * @param string $sort_by сортировка по
     * @param string $sort_type тип сортировки
     * @param int $offset
     * @param int|null $limit
     */
    public function __construct(
        /** ID семестра */
        #[Assert\Type('array', message: 'Значение должно быть массивом')]
        #[Assert\All([
            new Assert\Type('string', message: 'Значение должно быть строкой'),
            new Assert\NotBlank(message: 'Значение не должно быть пустым'),
            new Assert\Uuid(message: 'Значение должно быть валидным uuid'),
        ])]
        public array|null $semester_ids = null,
        /** Название для фильтрации */
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[Assert\Length(min: 1, max: 255, minMessage: 'Имя должно быть длиннее 1 символа', maxMessage: 'Имя должно быть короче 255 символов')]
        #[Assert\NotBlank(message: 'Значение не должно быть пустым', allowNull: true)]
        public string|null $name = null,
        /** Сортировка по */
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[Assert\Choice(choices: SubjectService::GET_ALL_SORT, message: 'Значение должно входить в список допустимых')]
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
        public int|null $limit = 20,
    ) {
    }
}
