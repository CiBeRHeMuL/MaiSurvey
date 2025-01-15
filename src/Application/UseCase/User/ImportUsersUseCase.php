<?php

namespace App\Application\UseCase\User;

use App\Domain\Dto\User\CreatedUsersInfo;
use App\Domain\Dto\User\ImportDto;
use App\Domain\Service\User\UserImporter;
use Psr\Log\LoggerInterface;

class ImportUsersUseCase
{
    private LoggerInterface $logger;

    public function __construct(
        private UserImporter $userImporter,
        LoggerInterface $logger,
    ) {
        $this->setLogger($logger);
    }

    public function setLogger(LoggerInterface $logger): ImportUsersUseCase
    {
        $this->logger = $logger;
        $this->userImporter->setLogger($logger);
        return $this;
    }

    public function execute(ImportDto $dto): CreatedUsersInfo
    {
        return $this
            ->userImporter
            ->import($dto);
    }
}
