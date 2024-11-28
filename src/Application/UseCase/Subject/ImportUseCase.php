<?php

namespace App\Application\UseCase\Subject;

use App\Domain\Dto\Subject\ImportDto;
use App\Domain\Service\Subject\SubjectsImporter;
use Psr\Log\LoggerInterface;

class ImportUseCase
{
    private LoggerInterface $logger;

    public function __construct(
        private SubjectsImporter $subjectImporter,
        LoggerInterface $logger,
    ) {
        $this->setLogger($logger);
    }

    public function setLogger(LoggerInterface $logger): ImportUseCase
    {
        $this->logger = $logger;
        $this->subjectImporter->setLogger($logger);
        return $this;
    }

    public function execute(ImportDto $dto): int
    {
        return $this
            ->subjectImporter
            ->import($dto);
    }
}
