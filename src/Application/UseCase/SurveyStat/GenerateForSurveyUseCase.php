<?php

namespace App\Application\UseCase\SurveyStat;

use App\Domain\Entity\Survey;
use App\Domain\Service\SurveyStat\StatRefresherInterface;
use Psr\Log\LoggerInterface;

class GenerateForSurveyUseCase
{
    private LoggerInterface $logger;

    public function __construct(
        LoggerInterface $logger,
        private StatRefresherInterface $statRefresher,
    ) {
        $this->setLogger($logger);
    }

    public function setLogger(LoggerInterface $logger): GenerateForSurveyUseCase
    {
        $this->logger = $logger;
        $this->statRefresher->setLogger($logger);
        return $this;
    }

    public function execute(Survey $survey): void
    {
        $this->statRefresher->refreshStats([$survey]);
    }
}
