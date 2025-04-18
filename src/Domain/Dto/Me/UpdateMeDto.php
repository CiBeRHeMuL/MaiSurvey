<?php

namespace App\Domain\Dto\Me;

readonly class UpdateMeDto
{
    public function __construct(
        private string $firstName,
        private string $lastName,
        private string|null $patronymic,
    ) {
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getPatronymic(): ?string
    {
        return $this->patronymic;
    }
}
