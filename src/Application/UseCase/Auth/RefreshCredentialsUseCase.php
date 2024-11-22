<?php

namespace App\Application\UseCase\Auth;

use App\Domain\Dto\Auth\RefreshCredentialsDto;
use App\Domain\Entity\User;
use App\Domain\Service\Auth\AuthService;
use Psr\Log\LoggerInterface;

class RefreshCredentialsUseCase
{
    private LoggerInterface $logger;

    public function __construct(
        private AuthService $authService,
        LoggerInterface $logger,
    ) {
        $this->setLogger($logger);
    }

    public function setLogger(LoggerInterface $logger): RefreshCredentialsUseCase
    {
        $this->logger = $logger;
        $this->authService->setLogger($logger);
        return $this;
    }

    public function execute(RefreshCredentialsDto $dto): User
    {
        return $this
            ->authService
            ->refreshCredentials($dto);
    }
}
