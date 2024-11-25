<?php

namespace App\Domain\Dto\UserDataGroup;

use App\Domain\Entity\Group;
use App\Domain\Entity\UserData;

readonly class CreateUserDataGroupDto
{
    public function __construct(
        private UserData $userData,
        private Group $group,
    ) {
    }

    public function getUserData(): UserData
    {
        return $this->userData;
    }

    public function getGroup(): Group
    {
        return $this->group;
    }
}
