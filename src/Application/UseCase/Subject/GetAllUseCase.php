<?php

namespace App\Application\UseCase\Subject;

use App\Application\Dto\Subject\GetAllSubjectsDto;
use App\Domain\DataProvider\DataProviderInterface;
use App\Domain\Dto\Subject\GetAllSubjectsDto as DomainGetAllSubjectsDto;
use App\Domain\Entity\Subject;
use App\Domain\Enum\SortTypeEnum;
use App\Domain\Service\Subject\SubjectService;
use Psr\Log\LoggerInterface;

class GetAllUseCase
{
    private LoggerInterface $logger;

    public function __construct(
        private SubjectService $subjectService,
        LoggerInterface $logger,
    ) {
        $this->setLogger($logger);
    }

    public function setLogger(LoggerInterface $logger): GetAllUseCase
    {
        $this->logger = $logger;
        $this->subjectService->setLogger($logger);
        return $this;
    }

    /**
     * @param GetAllSubjectsDto $dto
     *
     * @return DataProviderInterface<Subject>
     */
    public function execute(GetAllSubjectsDto $dto): DataProviderInterface
    {
        return $this
            ->subjectService
            ->getAll(
                new DomainGetAllSubjectsDto(
                    $dto->name,
                    $dto->sort_by,
                    SortTypeEnum::from($dto->sort_type),
                    $dto->offset,
                    $dto->limit,
                ),
            );
    }
}
