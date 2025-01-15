<?php

namespace App\Domain\Dto\User;

readonly class CreatedUsersInfo
{
    public function __construct(
        private int $count,
        private GetAllUsersDto|null $getAllUsersDto = null,
    ) {
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function getGetAllUsersDto(): GetAllUsersDto|null
    {
        return $this->getAllUsersDto;
    }
}
