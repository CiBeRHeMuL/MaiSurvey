<?php

namespace App\Application\UseCase\Semester;

use App\Application\Dto\Semester\CreateSemesterDto;
use App\Domain\Dto\Semester\CreateSemesterDto as DomainCreateSemesterDto;
use App\Domain\Entity\Semester;
use App\Domain\Service\Semester\SemesterService;
use Psr\Log\LoggerInterface;

class CreateSemesterUseCase
{
    private LoggerInterface $logger;

    public function __construct(
        private SemesterService $semesterService,
        LoggerInterface $logger,
    ) {
        $this->setLogger($logger);
    }

    public function setLogger(LoggerInterface $logger): CreateSemesterUseCase
    {
        $this->logger = $logger;
        $this->semesterService->setLogger($logger);
        return $this;
    }

    public function execute(CreateSemesterDto $dto): Semester
    {
        return $this
            ->semesterService
            ->create(
                new DomainCreateSemesterDto(
                    $dto->year,
                    $dto->spring,
                ),
            );
    }
}
