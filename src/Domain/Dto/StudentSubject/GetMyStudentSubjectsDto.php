<?php

namespace App\Domain\Dto\StudentSubject;

use App\Domain\Enum\SortTypeEnum;
use Symfony\Component\Uid\Uuid;

readonly class GetMyStudentSubjectsDto
{
    /**
     * @param bool|null $actual
     * @param Uuid[]|null $subjectIds
     * @param Uuid[]|null $teacherIds
     * @param string $sortBy
     * @param SortTypeEnum $sortType
     * @param int $offset
     * @param int|null $limit
     */
    public function __construct(
        private bool|null $actual = null,
        private array|null $subjectIds = null,
        private array|null $teacherIds = null,
        private string $sortBy = 'name',
        private SortTypeEnum $sortType = SortTypeEnum::Asc,
        private int $offset = 0,
        private int|null $limit = 100,
    ) {
    }

    public function getActual(): bool|null
    {
        return $this->actual;
    }

    public function getSubjectIds(): array|null
    {
        return $this->subjectIds;
    }

    public function getTeacherIds(): array|null
    {
        return $this->teacherIds;
    }

    public function getSortBy(): string
    {
        return $this->sortBy;
    }

    public function getSortType(): SortTypeEnum
    {
        return $this->sortType;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function getLimit(): int|null
    {
        return $this->limit;
    }
}
