<?php

namespace App\Application\UseCase\User;

use App\Application\Dto\User\UpdateUserDto;
use App\Domain\Dto\User\UpdateUserDto as DomainUpdateUserDto;
use App\Domain\Entity\User;
use App\Domain\Enum\RoleEnum;
use App\Domain\Enum\UserStatusEnum;
use App\Domain\Exception\ErrorException;
use App\Domain\Service\User\UserService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;

class UpdateUserUseCase
{
    private LoggerInterface $logger;

    public function __construct(
        LoggerInterface $logger,
        private UserService $userService,
    ) {
        $this->setLogger($logger);
    }

    public function setLogger(LoggerInterface $logger): UpdateUserUseCase
    {
        $this->logger = $logger;
        $this->userService->setLogger($logger);
        return $this;
    }

    public function execute(Uuid $id, UpdateUserDto $dto, User $updater): User
    {
        $user = $this->userService->getById($id);
        if (!$user) {
            throw ErrorException::new('Пользователь не найден', 404);
        }

        return $this
            ->userService
            ->updateUser(
                $user,
                new DomainUpdateUserDto(
                    array_map(RoleEnum::from(...), $dto->roles),
                    UserStatusEnum::from($dto->status),
                    $dto->deleted,
                    $dto->need_change_password,
                ),
                $updater,
            );
    }
}
