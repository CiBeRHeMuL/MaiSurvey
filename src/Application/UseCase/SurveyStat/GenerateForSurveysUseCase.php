<?php

namespace App\Application\UseCase\SurveyStat;

use App\Domain\Entity\Survey;
use App\Domain\Service\SurveyStat\SurveyStatService;
use Psr\Log\LoggerInterface;
use Throwable;

class GenerateForSurveysUseCase
{
    private LoggerInterface $logger;

    public function __construct(
        LoggerInterface $logger,
        private SurveyStatService $statService,
    ) {
        $this->setLogger($logger);
    }

    public function setLogger(LoggerInterface $logger): GenerateForSurveysUseCase
    {
        $this->logger = $logger;
        $this->statService->setLogger($logger);
        return $this;
    }

    /**
     * @param Survey[]|null $surveys
     * @param bool $force обновить все опросы принудительно
     *
     * @return int
     * @throws Throwable
     */
    public function execute(array|null $surveys = null, bool $force = false): int
    {
        return $this
            ->statService
            ->refreshStats($surveys, true, $force);
    }
}
