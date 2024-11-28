<?php

namespace App\Application\UseCase\UserData;

use App\Domain\Dto\UserData\ImportDto;
use App\Domain\Service\UserData\UserDataImporter;
use Psr\Log\LoggerInterface;

class ImportUseCase
{
    private LoggerInterface $logger;

    public function __construct(
        private UserDataImporter $userDataImporter,
        LoggerInterface $logger,
    ) {
        $this->setLogger($logger);
    }

    public function setLogger(LoggerInterface $logger): ImportUseCase
    {
        $this->logger = $logger;
        $this->userDataImporter->setLogger($logger);
        return $this;
    }

    public function execute(ImportDto $dto): int
    {
        return $this
            ->userDataImporter
            ->import($dto);
    }
}
