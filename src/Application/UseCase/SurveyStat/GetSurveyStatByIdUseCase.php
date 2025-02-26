<?php

namespace App\Application\UseCase\SurveyStat;

use App\Domain\Entity\SurveyStat;
use App\Domain\Service\SurveyStat\SurveyStatService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;

class GetSurveyStatByIdUseCase
{
    private LoggerInterface $logger;

    public function __construct(
        LoggerInterface $logger,
        private SurveyStatService $surveyStatService,
    ) {
        $this->setLogger($logger);
    }

    public function setLogger(LoggerInterface $logger): GetSurveyStatByIdUseCase
    {
        $this->logger = $logger;
        $this->surveyStatService->setLogger($logger);
        return $this;
    }

    public function execute(Uuid $surveyId): SurveyStat|null
    {
        return $this
            ->surveyStatService
            ->getForSurvey($surveyId);
    }
}
