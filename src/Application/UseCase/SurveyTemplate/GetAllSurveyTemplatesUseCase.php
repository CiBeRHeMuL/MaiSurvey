<?php

namespace App\Application\UseCase\SurveyTemplate;

use App\Application\Dto\SurveyTemplate\GetAllSurveyTemplatesDto;
use App\Domain\DataProvider\DataProviderInterface;
use App\Domain\Dto\SurveyTemplate\GetAllSurveyTemplatesDto as DomainGetAllSurveyTemplatesDto;
use App\Domain\Entity\SurveyTemplate;
use App\Domain\Enum\SortTypeEnum;
use App\Domain\Service\SurveyTemplate\SurveyTemplateService;
use Psr\Log\LoggerInterface;

class GetAllSurveyTemplatesUseCase
{
    private LoggerInterface $logger;

    public function __construct(
        private SurveyTemplateService $surveyService,
        LoggerInterface $logger,
    ) {
        $this->setLogger($logger);
    }

    public function setLogger(LoggerInterface $logger): GetAllSurveyTemplatesUseCase
    {
        $this->logger = $logger;
        $this->surveyService->setLogger($logger);
        return $this;
    }

    /**
     * @param GetAllSurveyTemplatesDto $dto
     *
     * @return DataProviderInterface<SurveyTemplate>
     */
    public function execute(GetAllSurveyTemplatesDto $dto): DataProviderInterface
    {
        return $this
            ->surveyService
            ->getAll(
                new DomainGetAllSurveyTemplatesDto(
                    $dto->name,
                    $dto->limit,
                    $dto->offset,
                    $dto->sort_by,
                    SortTypeEnum::from($dto->sort_type),
                ),
            );
    }
}
