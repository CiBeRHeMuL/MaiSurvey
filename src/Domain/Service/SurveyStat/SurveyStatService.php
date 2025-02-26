<?php

namespace App\Domain\Service\SurveyStat;

use App\Domain\Entity\SurveyStat;
use App\Domain\Repository\SurveyStatRepositoryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;

class SurveyStatService
{
    private LoggerInterface $logger;

    public function __construct(
        LoggerInterface $logger,
        private SurveyStatRepositoryInterface $surveyStatRepository,
    ) {
        $this->setLogger($logger);
    }

    public function setLogger(LoggerInterface $logger): SurveyStatService
    {
        $this->logger = $logger;
        return $this;
    }

    public function getForSurvey(Uuid $surveyId): SurveyStat|null
    {
        return $this
            ->surveyStatRepository
            ->findForSurvey($surveyId);
    }
}
