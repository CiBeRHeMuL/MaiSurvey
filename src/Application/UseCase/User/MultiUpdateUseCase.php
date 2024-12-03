<?php

namespace App\Application\UseCase\User;

use App\Domain\Dto\User\MultiUpdateDto;
use App\Domain\Service\User\UserMultiUpdater;
use Psr\Log\LoggerInterface;

class MultiUpdateUseCase
{
    private LoggerInterface $logger;

    public function __construct(
        private UserMultiUpdater $userMultiUpdater,
        LoggerInterface $logger,
    ) {
        $this->setLogger($logger);
    }

    public function setLogger(LoggerInterface $logger): MultiUpdateUseCase
    {
        $this->logger = $logger;
        $this->userMultiUpdater->setLogger($logger);
        return $this;
    }

    public function execute(MultiUpdateDto $dto): int
    {
        return $this
            ->userMultiUpdater
            ->multiUpdate($dto);
    }
}
