<?php

namespace App\Domain\Dto\SurveyTemplate;

use App\Domain\Enum\SortTypeEnum;

readonly class GetAllSurveyTemplatesDto
{
    /**
     * @param string|null $name
     * @param int|null $limit
     * @param int $offset
     * @param string $sortBy
     * @param SortTypeEnum $sortType
     */
    public function __construct(
        private string|null $name = null,
        private int|null $limit = 100,
        private int $offset = 0,
        private string $sortBy = 'created_at',
        private SortTypeEnum $sortType = SortTypeEnum::Desc,
    ) {
    }

    public function getName(): string|null
    {
        return $this->name;
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
}
