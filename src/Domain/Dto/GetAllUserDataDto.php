<?php

namespace App\Domain\Dto;

use App\Domain\Enum\SortTypeEnum;
use Symfony\Component\Uid\Uuid;

readonly class GetAllUserDataDto
{
    /**
     * @param string|null $name
     * @param bool|null $withGroup
     * @param bool|null $withUser
     * @param Uuid[]|null $groupIds
     * @param string $sortBy
     * @param SortTypeEnum $sortType
     * @param int $offset
     * @param int|null $limit
     */
    public function __construct(
        private string|null $name = null,
        private bool|null $withGroup = false,
        private bool|null $withUser = null,
        private array|null $groupIds = null,
        private string $sortBy = 'name',
        private SortTypeEnum $sortType = SortTypeEnum::Asc,
        private int $offset = 0,
        private int|null $limit = 20,
    ) {
    }

    public function getName(): string|null
    {
        return $this->name;
    }

    public function withGroup(): bool|null
    {
        return $this->withGroup;
    }

    public function withUser(): bool|null
    {
        return $this->withUser;
    }

    public function getGroupIds(): array|null
    {
        return $this->groupIds;
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
