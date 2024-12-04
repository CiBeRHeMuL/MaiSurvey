<?php

namespace App\Application\UseCase\User;

use App\Application\Dto\User\GetAllUsersDto;
use App\Domain\DataProvider\DataProviderInterface;
use App\Domain\Dto\User\GetAllUsersDto as DomainGetAllUsersDto;
use App\Domain\Entity\User;
use App\Domain\Enum\RoleEnum;
use App\Domain\Enum\SortTypeEnum;
use App\Domain\Enum\UserStatusEnum;
use App\Domain\Service\User\UserService;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;

class GetAllUseCase
{
    private LoggerInterface $logger;

    public function __construct(
        private UserService $userService,
        LoggerInterface $logger,
    ) {
        $this->setLogger($logger);
    }

    public function setLogger(LoggerInterface $logger): GetAllUseCase
    {
        $this->logger = $logger;
        $this->userService->setLogger($logger);
        return $this;
    }

    /**
     * @param GetAllUsersDto $dto
     *
     * @return DataProviderInterface<User>
     */
    public function execute(GetAllUsersDto $dto): DataProviderInterface
    {
        return $this
            ->userService
            ->getAll(
                new DomainGetAllUsersDto(
                    $dto->roles !== null ? array_map(RoleEnum::from(...), $dto->roles) : null,
                    $dto->name,
                    $dto->email,
                    $dto->deleted,
                    UserStatusEnum::tryFrom((string)$dto->status),
                    $dto->group_ids !== null ? array_map(fn(string $i) => new Uuid($i), $dto->group_ids) : null,
                    $dto->with_group,
                    $dto->created_from !== null ? new DateTimeImmutable($dto->created_from) : null,
                    $dto->created_to !== null ? new DateTimeImmutable($dto->created_to) : null,
                    $dto->sort_by,
                    SortTypeEnum::from($dto->sort_type),
                    $dto->offset,
                    $dto->limit,
                ),
            );
    }
}
