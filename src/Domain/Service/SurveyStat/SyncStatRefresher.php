<?php

namespace App\Domain\Service\SurveyStat;

use App\Domain\Entity\Survey;
use App\Domain\Exception\ErrorException;
use Psr\Log\LoggerInterface;
use Throwable;

class SyncStatRefresher implements StatRefresherInterface
{
    private LoggerInterface $logger;

    public function __construct(
        LoggerInterface $logger,
        private SurveyStatService $surveyStatService,
    ) {
        $this->setLogger($logger);
    }

    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    public function setLogger(LoggerInterface $logger): static
    {
        $this->logger = $logger;
        $this->surveyStatService->setLogger($logger);
        return $this;
    }

    public function refreshStat(Survey $survey): void
    {
        try {
            $this->surveyStatService->refreshStat($survey, false);
        } catch (Throwable $e) {
            $this->logger->error($e);
            throw ErrorException::new("Не удалось обновить статистику по опросу {$survey->getId()->toRfc4122()}");
        }
    }
}
