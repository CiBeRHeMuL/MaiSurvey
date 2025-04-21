<?php

namespace App\Domain\Dto\StudentSubject;

use App\Domain\Enum\SortTypeEnum;
use Symfony\Component\Uid\Uuid;

readonly class GetAllStudentSubjectsDto
{
    /**
     * @param Uuid[]|null $userIds
     * @param Uuid[]|null $teacherIds
     * @param Uuid[]|null $subjectIds
     * @param string $sortBy
     * @param SortTypeEnum $sortType
     * @param int $offset
     * @param int|null $limit
     */
    public function __construct(
        private array|null $userIds = null,
        private array|null $teacherIds = null,
        private array|null $subjectIds = null,
        private string $sortBy = 'name',
        private SortTypeEnum $sortType = SortTypeEnum::Asc,
        private int $offset = 0,
        private int|null $limit = 100,
    ) {
    }

    public function getUserIds(): array|null
    {
        return $this->userIds;
    }

    public function getTeacherIds(): array|null
    {
        return $this->teacherIds;
    }

    public function getSubjectIds(): array|null
    {
        return $this->subjectIds;
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
