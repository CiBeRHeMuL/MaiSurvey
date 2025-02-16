<?php

namespace App\Domain\Dto\Survey;

use App\Domain\Enum\SortTypeEnum;
use Symfony\Component\Uid\Uuid;

readonly class GetMySurveysDto
{
    /**
     * @param Uuid[]|null $subjectIds
     * @param bool|null $completed
     * @param int|null $limit
     * @param int $offset
     * @param string $sortBy
     * @param SortTypeEnum $sortType
     * @param bool|null $actual
     */
    public function __construct(
        private array|null $subjectIds = null,
        private bool|null $completed = null,
        private int|null $limit = 100,
        private int $offset = 0,
        private string $sortBy = 'created_at',
        private SortTypeEnum $sortType = SortTypeEnum::Desc,
        private bool|null $actual = true,
    ) {
    }

    public function getSubjectIds(): array|null
    {
        return $this->subjectIds;
    }

    public function getCompleted(): bool|null
    {
        return $this->completed;
    }

    public function getLimit(): int|null
    {
        return $this->limit;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function getSortBy(): string
    {
        return $this->sortBy;
    }

    public function getSortType(): SortTypeEnum
    {
        return $this->sortType;
    }

    public function getActual(): bool|null
    {
        return $this->actual;
    }
}
