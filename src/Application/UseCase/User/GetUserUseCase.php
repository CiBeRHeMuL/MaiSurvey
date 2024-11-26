<?php

namespace App\Application\UseCase\User;

use App\Domain\Entity\User;
use App\Domain\Service\User\UserService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;

class GetUserUseCase
{
    private LoggerInterface $logger;

    public function __construct(
        private UserService $userService,
        LoggerInterface $logger,
    ) {
        $this->setLogger($logger);
    }

    public function setLogger(LoggerInterface $logger): GetUserUseCase
    {
        $this->logger = $logger;
        $this->userService->setLogger($logger);
        return $this;
    }

    public function execute(Uuid $id): User|null
    {
        return $this->userService->getById($id);
    }
}
