<?php

namespace App\Domain\Dto\Subject;

use App\Domain\Enum\SortTypeEnum;

readonly class GetAllSubjectsDto
{
    /**
     * @param string|null $name
     * @param string $sortBy
     * @param SortTypeEnum $sortType
     * @param int $offset
     * @param int|null $limit
     */
    public function __construct(
        private string|null $name = null,
        private string $sortBy = 'name',
        private SortTypeEnum $sortType = SortTypeEnum::Asc,
        private int $offset = 0,
        private int|null $limit = 100,
    ) {
    }

    public function getName(): string|null
    {
        return $this->name;
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
