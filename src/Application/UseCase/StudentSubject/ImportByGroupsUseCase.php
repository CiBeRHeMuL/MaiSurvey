<?php

namespace App\Application\UseCase\StudentSubject;

use App\Domain\Dto\StudentSubject\CreatedStudentSubjectsInfo;
use App\Domain\Dto\StudentSubject\ImportByGroupsDto;
use App\Domain\Service\StudentSubject\StudentSubjectsByGroupsImporter;
use Psr\Log\LoggerInterface;

class ImportByGroupsUseCase
{
    private LoggerInterface $logger;

    public function __construct(
        LoggerInterface $logger,
        private StudentSubjectsByGroupsImporter $studentSubjectsImporter,
    ) {
        $this->setLogger($logger);
    }

    public function setLogger(LoggerInterface $logger): ImportByGroupsUseCase
    {
        $this->logger = $logger;
        $this->studentSubjectsImporter->setLogger($logger);
        return $this;
    }

    public function execute(ImportByGroupsDto $dto): CreatedStudentSubjectsInfo
    {
        return $this
            ->studentSubjectsImporter
            ->import($dto);
    }
}
