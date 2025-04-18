<?php

namespace App\Domain\Dto\User;

use App\Domain\Enum\RoleEnum;
use App\Domain\ValueObject\Email;
use SensitiveParameter;
use Symfony\Component\Uid\Uuid;

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
        private Uuid|null $groupId,
        private bool $needChangePassword =  false,
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

    public function getGroupId(): Uuid|null
    {
        return $this->groupId;
    }

    public function isNeedChangePassword(): bool
    {
        return $this->needChangePassword;
    }
}
