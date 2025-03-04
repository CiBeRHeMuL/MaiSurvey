<?php

namespace App\Application\UseCase\SurveyStat;

use App\Domain\Service\SurveyStat\SurveyStatService;
use Psr\Log\LoggerInterface;

class GenerateForSurveysUseCase
{
    private LoggerInterface $logger;

    public function __construct(
        LoggerInterface $logger,
        private SurveyStatService $surveyStatService,
    ) {
        $this->setLogger($logger);
    }

    public function setLogger(LoggerInterface $logger): GenerateForSurveysUseCase
    {
        $this->logger = $logger;
        $this->surveyStatService->setLogger($logger);
        return $this;
    }

    public function execute(array|null $surveyIds = null): int
    {
        return $this->surveyStatService->refreshStats($surveyIds, true);
    }
}
