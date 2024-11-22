<?php

namespace App\Application\UseCase\Auth;

use App\Application\Dto\Auth\SignInDto;
use App\Domain\Dto\Auth\SignInDto as DomainSignInDto;
use App\Domain\Entity\User;
use App\Domain\Service\Auth\AuthService;
use App\Domain\ValueObject\Email;
use Psr\Log\LoggerInterface;

class SignInUseCase
{
    private LoggerInterface $logger;

    public function __construct(
        private AuthService $authService,
        LoggerInterface $logger,
    ) {
        $this->setLogger($logger);
    }

    public function setLogger(LoggerInterface $logger): SignInUseCase
    {
        $this->logger = $logger;
        $this->authService->setLogger($this->logger);
        return $this;
    }

    public function execute(SignInDto $dto): User
    {
        return $this
            ->authService
            ->signIn(
                new DomainSignInDto(
                    new Email($dto->email),
                    $dto->password,
                ),
            );
    }
}
