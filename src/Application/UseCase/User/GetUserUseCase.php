<?php

namespace App\Application\UseCase\User;

use App\Domain\Entity\User;
use App\Domain\Service\User\UserService;
use Symfony\Component\Uid\Uuid;

class GetUserUseCase
{
    public function __construct(
        private UserService $userService,
    ) {
    }

    public function execute(Uuid $id): User|null
    {
        return $this->userService->getById($id);
    }
}
