<?php

namespace App\Application\UseCase\User;

use App\Application\Dto\User\CreateFullUserDto;
use App\Domain\Dto\User\CreateFullUserDto as DomainCreateFullUserDto;
use App\Domain\Entity\User;
use App\Domain\Enum\RoleEnum;
use App\Domain\Service\User\FullUserService;
use App\Domain\ValueObject\Email;
use Psr\Log\LoggerInterface;

class CreateUserUseCase
{
    private LoggerInterface $logger;

    public function __construct(
        LoggerInterface $logger,
        private FullUserService $fullUserService,
    ) {
        $this->setLogger($logger);
    }

    public function setLogger(LoggerInterface $logger): CreateUserUseCase
    {
        $this->logger = $logger;
        $this->fullUserService->setLogger($logger);
        return $this;
    }

    public function execute(CreateFullUserDto $dto): User
    {
        return $this
            ->fullUserService
            ->createFullUser(
                new DomainCreateFullUserDto(
                    new Email($dto->email),
                    $dto->password,
                    RoleEnum::from($dto->role),
                    $dto->first_name,
                    $dto->last_name,
                    $dto->patronymic,
                    $dto->group,
                ),
            );
    }
}
