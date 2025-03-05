<?php

namespace App\Domain\Dto\Survey;

use App\Domain\Enum\SortTypeEnum;
use Symfony\Component\Uid\Uuid;

readonly class GetSurveysDto
{
    /**
     * @param Uuid[]|null $subjectIds
     * @param string|null $title
     * @param int|null $limit
     * @param int $offset
     * @param string $sortBy
     * @param SortTypeEnum $sortType
     * @param bool|null $actual
     */
    public function __construct(
        private array|null $subjectIds = null,
        private string|null $title = null,
        private int|null $limit = 100,
        private int $offset = 0,
        private string $sortBy = 'created_at',
        private SortTypeEnum $sortType = SortTypeEnum::Desc,
        private bool|null $actual = null,
    ) {
    }

    public function getSubjectIds(): array|null
    {
        return $this->subjectIds;
    }

    public function getTitle(): string|null
    {
        return $this->title;
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

    public function getActual(): ?bool
    {
        return $this->actual;
    }
}
