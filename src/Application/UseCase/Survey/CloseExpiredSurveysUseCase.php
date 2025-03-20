<?php

namespace App\Application\UseCase\Survey;

use App\Application\Dto\Survey\GetSurveysDto;
use App\Domain\Enum\SurveyStatusEnum;
use App\Domain\Service\Survey\SurveyService;
use Psr\Log\LoggerInterface;

class CloseExpiredSurveysUseCase
{
    private LoggerInterface $logger;

    public function __construct(
        LoggerInterface $logger,
        private SurveyService $surveyService,
    ) {
        $this->setLogger($logger);
    }

    public function setLogger(LoggerInterface $logger): CloseExpiredSurveysUseCase
    {
        $this->logger = $logger;
        $this->surveyService->setLogger($logger);
        return $this;
    }

    public function execute(): int
    {
        return $this->surveyService->closeExpired();
    }
}
