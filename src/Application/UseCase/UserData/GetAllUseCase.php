<?php

namespace App\Application\UseCase\UserData;

use App\Application\Dto\UserData\GetAllUserDataDto;
use App\Domain\DataProvider\DataProviderInterface;
use App\Domain\Dto\GetAllUserDataDto as DomainGetAllUserDataDto;
use App\Domain\Enum\SortTypeEnum;
use App\Domain\Service\UserData\UserDataService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;

class GetAllUseCase
{
    private LoggerInterface $logger;

    public function __construct(
        private UserDataService $userDataService,
        LoggerInterface $logger,
    ) {
        $this->setLogger($logger);
    }

    public function setLogger(LoggerInterface $logger): GetAllUseCase
    {
        $this->logger = $logger;
        $this->userDataService->setLogger($logger);
        return $this;
    }

    public function execute(GetAllUserDataDto $dto): DataProviderInterface
    {
        return $this
            ->userDataService
            ->getAll(
                new DomainGetAllUserDataDto(
                    $dto->name,
                    $dto->only_with_group,
                    $dto->group_ids !== null
                        ? array_map(fn(string $e) => new Uuid($e), $dto->group_ids)
                        : null,
                    $dto->sort_by,
                    SortTypeEnum::from($dto->sort_type),
                    $dto->offset,
                    $dto->limit,
                ),
            );
    }
}
