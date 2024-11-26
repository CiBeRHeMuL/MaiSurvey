<?php

namespace App\Domain\Dto\User;

use App\Domain\Enum\RoleEnum;
use App\Domain\Enum\SortTypeEnum;
use App\Domain\Enum\UserStatusEnum;
use DateTimeImmutable;
use Symfony\Component\Uid\Uuid;

readonly class GetAllUsersDto
{
    /**
     * @param RoleEnum[]|null $roles
     * @param string|null $name
     * @param string|null $email
     * @param bool|null $deleted
     * @param UserStatusEnum|null $status
     * @param Uuid[]|null $groupIds
     * @param bool|null $withGroup
     * @param DateTimeImmutable|null $createdFrom
     * @param DateTimeImmutable|null $createdTo
     * @param string $sortBy
     * @param SortTypeEnum $sortType
     * @param int $offset
     * @param int|null $limit
     */
    public function __construct(
        private array|null $roles = null,
        private string|null $name = null,
        private string|null $email = null,
        private bool|null $deleted = null,
        private UserStatusEnum|null $status = null,
        private array|null $groupIds = null,
        private bool|null $withGroup = null,
        private DateTimeImmutable|null $createdFrom = null,
        private DateTimeImmutable|null $createdTo = null,
        private string $sortBy = 'name',
        private SortTypeEnum $sortType = SortTypeEnum::Asc,
        private int $offset = 0,
        private int|null $limit = 100,
    ) {
    }

    public function getRoles(): array|null
    {
        return $this->roles;
    }

    public function getName(): string|null
    {
        return $this->name;
    }

    public function getEmail(): string|null
    {
        return $this->email;
    }

    public function getDeleted(): bool|null
    {
        return $this->deleted;
    }

    public function getStatus(): UserStatusEnum|null
    {
        return $this->status;
    }

    public function getGroupIds(): array|null
    {
        return $this->groupIds;
    }

    public function getWithGroup(): bool|null
    {
        return $this->withGroup;
    }

    public function getCreatedFrom(): DateTimeImmutable|null
    {
        return $this->createdFrom;
    }

    public function getCreatedTo(): DateTimeImmutable|null
    {
        return $this->createdTo;
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
