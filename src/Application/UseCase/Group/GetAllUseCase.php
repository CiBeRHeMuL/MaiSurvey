<?php

namespace App\Application\UseCase\Group;

use App\Application\Dto\Group\GetAllGroupsDto;
use App\Domain\DataProvider\DataProviderInterface;
use App\Domain\Dto\Group\GetAllGroupsDto as DomainGetAllGroupsDto;
use App\Domain\Enum\SortTypeEnum;
use App\Domain\Service\Group\GroupService;
use Psr\Log\LoggerInterface;

class GetAllUseCase
{
    private LoggerInterface $logger;

    public function __construct(
        private GroupService $groupService,
        LoggerInterface $logger,
    ) {
        $this->setLogger($logger);
    }

    public function setLogger(LoggerInterface $logger): GetAllUseCase
    {
        $this->logger = $logger;
        $this->groupService->setLogger($logger);
        return $this;
    }

    public function execute(GetAllGroupsDto $dto): DataProviderInterface
    {
        return $this
            ->groupService
            ->getAll(
                new DomainGetAllGroupsDto(
                    $dto->name,
                    $dto->sort_by,
                    SortTypeEnum::from($dto->sort_type),
                    $dto->offset,
                    $dto->limit,
                ),
            );
    }
}
