<?php

namespace App\Application\UseCase\Auth;

use App\Application\Dto\Auth\SignUpStep1Dto;
use App\Domain\Dto\Auth\SignUpStep1Dto as DomainSignUpStep1Dto;
use App\Domain\Entity\User;
use App\Domain\Enum\RoleEnum;
use App\Domain\Service\Auth\AuthService;
use App\Domain\ValueObject\Email;
use Psr\Log\LoggerInterface;

class SignUpStep1UseCase
{
    private LoggerInterface $logger;

    public function __construct(
        private AuthService $authService,
        LoggerInterface $logger,
    ) {
        $this->setLogger($logger);
    }

    public function setLogger(LoggerInterface $logger): SignUpStep1UseCase
    {
        $this->logger = $logger;
        $this->authService->setLogger($this->logger);
        return $this;
    }

    public function execute(SignUpStep1Dto $dto): User
    {
        return $this
            ->authService
            ->signUpStep1(
                new DomainSignUpStep1Dto(
                    new Email($dto->email),
                    $dto->password,
                    $dto->repeat_password,
                    RoleEnum::Student,
                ),
            );
    }
}
