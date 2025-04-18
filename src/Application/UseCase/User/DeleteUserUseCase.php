<?php

namespace App\Application\UseCase\User;

use App\Domain\Entity\User;
use App\Domain\Exception\ErrorException;
use App\Domain\Service\User\UserService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;

class DeleteUserUseCase
{
    private LoggerInterface $logger;

    public function __construct(
        private UserService $userService,
        private GetUserUseCase $getUserUseCase,
        LoggerInterface $logger,
    ) {
        $this->setLogger($logger);
    }

    public function setLogger(LoggerInterface $logger): DeleteUserUseCase
    {
        $this->logger = $logger;
        $this->getUserUseCase->setLogger($logger);
        $this->userService->setLogger($logger);
        return $this;
    }

    public function execute(Uuid $id, User $updater): User
    {
        $user = $this->getUserUseCase->execute($id);
        if (!$user) {
            throw ErrorException::new('Пользователь не найден', 404);
        }
        return $this->userService->delete($user, $updater);
    }
}
