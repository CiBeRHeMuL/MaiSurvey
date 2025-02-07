<?php

namespace App\Application\UseCase\Semester;

use App\Application\Dto\Semester\CreateSemesterDto;
use App\Domain\Dto\Semester\CreateSemesterDto as DomainCreateSemesterDto;
use App\Domain\Service\Semester\SemesterService;
use Psr\Log\LoggerInterface;

class CreateSemestersUseCase
{
    private LoggerInterface $logger;

    public function __construct(
        private SemesterService $semesterService,
        LoggerInterface $logger,
    ) {
        $this->setLogger($logger);
    }

    public function setLogger(LoggerInterface $logger): CreateSemestersUseCase
    {
        $this->logger = $logger;
        $this->semesterService->setLogger($logger);
        return $this;
    }

    public function execute(array $dtos): int
    {
        return $this
            ->semesterService
            ->createMulti(
                array_map(
                    fn(CreateSemesterDto $dto) => new DomainCreateSemesterDto(
                        $dto->year,
                        $dto->spring,
                    ),
                    $dtos,
                ),
            );
    }
}
