<?php

namespace App\Application\UseCase\TeacherSubject;

use App\Domain\Dto\TeacherSubject\ImportDto;
use App\Domain\Service\TeacherSubject\TeacherSubjectsImporter;
use Psr\Log\LoggerInterface;

class ImportTeacherSubjectsUseCase
{
    private LoggerInterface $logger;

    public function __construct(
        private TeacherSubjectsImporter $teacherSubjectsImporter,
        LoggerInterface $logger,
    ) {
        $this->setLogger($logger);
    }

    public function setLogger(LoggerInterface $logger): ImportTeacherSubjectsUseCase
    {
        $this->logger = $logger;
        $this->teacherSubjectsImporter->setLogger($logger);
        return $this;
    }

    public function execute(ImportDto $dto): int
    {
        return $this
            ->teacherSubjectsImporter
            ->import($dto);
    }
}
