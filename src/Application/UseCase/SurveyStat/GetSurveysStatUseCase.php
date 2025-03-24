<?php

namespace App\Application\UseCase\SurveyStat;

use App\Application\Dto\Survey\GetSurveysDto;
use App\Domain\DataProvider\DataProviderInterface;
use App\Domain\Dto\Survey\GetSurveysDto as DomainGetSurveysDto;
use App\Domain\Entity\SurveyStat;
use App\Domain\Enum\SortTypeEnum;
use App\Domain\Service\SurveyStat\SurveyStatService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;

class GetSurveysStatUseCase
{
    private LoggerInterface $logger;

    public function __construct(
        LoggerInterface $logger,
        private SurveyStatService $surveyStatService,
    ) {
        $this->setLogger($logger);
    }

    public function setLogger(LoggerInterface $logger): GetSurveysStatUseCase
    {
        $this->logger = $logger;
        $this->surveyStatService->setLogger($logger);
        return $this;
    }

    /**
     * @param GetSurveysDto $dto
     * @return DataProviderInterface<SurveyStat>
     */
    public function execute(GetSurveysDto $dto): DataProviderInterface
    {
        return $this
            ->surveyStatService
            ->getForSurveys(
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
                ),
            );
    }
}
