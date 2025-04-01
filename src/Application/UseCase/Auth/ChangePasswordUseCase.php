<?php

namespace App\Application\UseCase\Auth;

use App\Application\Dto\Auth\ChangePasswordDto;
use App\Domain\Dto\Auth\ChangePasswordDto as DomainChangePasswordDto;
use App\Domain\Entity\User;
use App\Domain\Service\Auth\AuthService;
use Psr\Log\LoggerInterface;

class ChangePasswordUseCase
{
    private LoggerInterface $logger;

    public function __construct(
        private AuthService $authService,
        LoggerInterface $logger,
    ) {
        $this->setLogger($logger);
    }

    public function setLogger(LoggerInterface $logger): ChangePasswordUseCase
    {
        $this->logger = $logger;
        $this->authService->setLogger($logger);
        return $this;
    }

    public function execute(User $user, ChangePasswordDto $dto): User
    {
        return $this
            ->authService
            ->changePassword(
                $user,
                new DomainChangePasswordDto(
                    $dto->old_password,
                    $dto->new_password,
                    $dto->repeat_password,
                ),
            );
    }
}
