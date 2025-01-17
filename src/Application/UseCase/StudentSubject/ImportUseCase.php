<?php

namespace App\Application\UseCase\StudentSubject;

use App\Domain\Dto\StudentSubject\ImportDto;
use App\Domain\Service\StudentSubject\StudentSubjectsImporter;
use Psr\Log\LoggerInterface;

class ImportUseCase
{
    private LoggerInterface $logger;

    public function __construct(
        LoggerInterface $logger,
        private StudentSubjectsImporter $studentSubjectsImporter,
    ) {
        $this->setLogger($logger);
    }

    public function setLogger(LoggerInterface $logger): ImportUseCase
    {
        $this->logger = $logger;
        $this->studentSubjectsImporter->setLogger($logger);
        return $this;
    }

    public function execute(ImportDto $dto): int
    {
        return $this
            ->studentSubjectsImporter
            ->import($dto);
    }
}
