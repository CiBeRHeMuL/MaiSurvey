<?php

namespace App\Application\UseCase\Me;

use App\Domain\Entity\User;
use App\Domain\Service\User\UserService;
use Psr\Log\LoggerInterface;

class DeleteMeUseCase
{
    private LoggerInterface $logger;

    public function __construct(
        LoggerInterface $logger,
        private UserService $userService,
    ) {
        $this->setLogger($logger);
    }

    public function setLogger(LoggerInterface $logger): DeleteMeUseCase
    {
        $this->logger = $logger;
        $this->userService->setLogger($logger);
        return $this;
    }

    public function execute(User $me): User
    {
        return $this->userService->deleteMe($me);
    }
}
