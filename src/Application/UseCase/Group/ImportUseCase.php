<?php

namespace App\Application\UseCase\Group;

use App\Domain\Dto\Group\ImportDto;
use App\Domain\Service\Group\GroupsImporter;
use Psr\Log\LoggerInterface;

class ImportUseCase
{
    private LoggerInterface $logger;

    public function __construct(
        private GroupsImporter $groupImporter,
        LoggerInterface $logger,
    ) {
        $this->setLogger($logger);
    }

    public function setLogger(LoggerInterface $logger): ImportUseCase
    {
        $this->logger = $logger;
        $this->groupImporter->setLogger($logger);
        return $this;
    }

    public function execute(ImportDto $dto): int
    {
        return $this
            ->groupImporter
            ->import($dto);
    }
}
