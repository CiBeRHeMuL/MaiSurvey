<?php

namespace App\Domain\Dto\User;

use App\Domain\Enum\RoleEnum;
use App\Domain\ValueObject\Email;
use SensitiveParameter;

readonly class CreateFullUserDto
{
    public function __construct(
        private Email $email,
        #[SensitiveParameter]
        private string $password,
        private RoleEnum $role,
        private string $firstName,
        private string $lastName,
        private string|null $patronymic,
        private string|null $group,
    ) {
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
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

    public function getGroup(): string|null
    {
        return $this->group;
    }
}
