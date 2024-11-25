<?php

namespace App\Application\UseCase\Group;

use App\Application\Dto\Group\CreateGroupDto;
use App\Domain\Dto\Group\CreateGroupDto as DomainCreateGroupDto;
use App\Domain\Entity\Group;
use App\Domain\Service\Group\GroupService;
use Psr\Log\LoggerInterface;

class CreateUseCase
{
    private LoggerInterface $logger;

    public function __construct(
        LoggerInterface $logger,
        private GroupService $groupService,
    ) {
        $this->setLogger($logger);
    }

    public function setLogger(LoggerInterface $logger): CreateUseCase
    {
        $this->logger = $logger;
        $this->groupService->setLogger($logger);
        return $this;
    }

    public function execute(CreateGroupDto $dto): Group
    {
        return $this
            ->groupService
            ->create(
                new DomainCreateGroupDto(
                    $dto->name,
                ),
            );
    }
}
