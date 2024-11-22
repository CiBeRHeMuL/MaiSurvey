<?php

namespace App\Application\UseCase\Auth;

use App\Application\Dto\Auth\SignUpStep2Dto;
use App\Domain\Dto\Auth\SignUpStep2Dto as DomainSignUpStep2Dto;
use App\Domain\Entity\User;
use App\Domain\Service\Auth\AuthService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;

class SignUpStep2UseCase
{
    private LoggerInterface $logger;

    public function __construct(
        private AuthService $authService,
        LoggerInterface $logger,
    ) {
        $this->setLogger($logger);
    }

    public function setLogger(LoggerInterface $logger): SignUpStep2UseCase
    {
        $this->logger = $logger;
        $this->authService->setLogger($this->logger);
        return $this;
    }

    public function execute(SignUpStep2Dto $dto): User
    {
        return $this
            ->authService
            ->signUpStep2(
                new DomainSignUpStep2Dto(
                    new Uuid($dto->user_data_id),
                ),
            );
    }
}
