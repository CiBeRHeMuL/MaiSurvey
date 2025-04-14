<?php

namespace App\Application\UseCase\Survey;

use App\Application\Dto\Survey\GetSurveysDto;
use App\Domain\DataProvider\DataProviderInterface;
use App\Domain\Dto\Survey\GetSurveysDto as DomainGetSurveysDto;
use App\Domain\Entity\Survey;
use App\Domain\Enum\SortTypeEnum;
use App\Domain\Enum\SurveyStatusEnum;
use App\Domain\Service\Survey\SurveyService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;

class GetSurveysUseCase
{
    private LoggerInterface $logger;

    public function __construct(
        private SurveyService $surveyService,
        LoggerInterface $logger,
    ) {
        $this->setLogger($logger);
    }

    public function setLogger(LoggerInterface $logger): GetSurveysUseCase
    {
        $this->logger = $logger;
        $this->surveyService->setLogger($logger);
        return $this;
    }

    /**
     * @param GetSurveysDto $dto
     *
     * @return DataProviderInterface<Survey>
     */
    public function execute(GetSurveysDto $dto): DataProviderInterface
    {
        return $this
            ->surveyService
            ->getAll(
                new DomainGetSurveysDto(
                    $dto->subject_ids !== null
                        ? array_map(fn(string $id) => new Uuid($id), $dto->subject_ids)
                        : null,
                    $dto->title,
                    $dto->limit,
                    $dto->offset,
                    $dto->sort_by,
                    SortTypeEnum::from($dto->sort_type),
                    $dto->actual,
                    $dto->statuses !== null
                        ? array_map(SurveyStatusEnum::from(...), $dto->statuses)
                        : null,
                ),
            );
    }
}
