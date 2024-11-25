<?php

namespace App\Domain\Dto\UserData;

use App\Domain\Entity\Group;
use App\Domain\Enum\RoleEnum;

readonly class CreateUserDataDto
{
    public function __construct(
        private RoleEnum $role,
        private string $firstName,
        private string $lastName,
        private string|null $patronymic,
        private Group|null $group,
    ) {
    }

    public function getRole(): RoleEnum
    {
        return $this->role;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getPatronymic(): string|null
    {
        return $this->patronymic;
    }

    public function getGroup(): Group|null
    {
        return $this->group;
    }
}
