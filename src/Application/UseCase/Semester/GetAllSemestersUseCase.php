<?php

namespace App\Application\UseCase\Semester;

use App\Application\Dto\Semester\GetAllSemestersDto;
use App\Domain\DataProvider\DataProviderInterface;
use App\Domain\Dto\Semester\GetAllSemestersDto as DomainGetAllSemestersDto;
use App\Domain\Entity\Semester;
use App\Domain\Enum\SortTypeEnum;
use App\Domain\Service\Semester\SemesterService;
use Psr\Log\LoggerInterface;

class GetAllSemestersUseCase
{
    private LoggerInterface $logger;

    public function __construct(
        private SemesterService $semesterService,
        LoggerInterface $logger,
    ) {
        $this->setLogger($logger);
    }

    public function setLogger(LoggerInterface $logger): GetAllSemestersUseCase
    {
        $this->logger = $logger;
        $this->semesterService->setLogger($logger);
        return $this;
    }

    /**
     * @return DataProviderInterface<Semester>
     */
    public function execute(GetAllSemestersDto $dto): DataProviderInterface
    {
        return $this
            ->semesterService
            ->getAll(
                new DomainGetAllSemestersDto(
                    $dto->sort_by,
                    SortTypeEnum::from($dto->sort_type),
                    $dto->offset,
                    $dto->limit,
                ),
            );
    }
}
