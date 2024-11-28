<?php

namespace App\Application\UseCase\Subject;

use App\Application\Dto\Subject\CreateSubjectDto;
use App\Domain\Dto\Subject\CreateSubjectDto as DomainCreateSubjectDto;
use App\Domain\Entity\Subject;
use App\Domain\Service\Subject\SubjectService;
use Psr\Log\LoggerInterface;

class CreateUseCase
{
    private LoggerInterface $logger;

    public function __construct(
        LoggerInterface $logger,
        private SubjectService $subjectService,
    ) {
        $this->setLogger($logger);
    }

    public function setLogger(LoggerInterface $logger): CreateUseCase
    {
        $this->logger = $logger;
        $this->subjectService->setLogger($logger);
        return $this;
    }

    public function execute(CreateSubjectDto $dto): Subject
    {
        return $this
            ->subjectService
            ->create(
                new DomainCreateSubjectDto(
                    $dto->name,
                ),
            );
    }
}
