<?php

namespace App\Domain\Service\SurveyStat;

use App\Domain\Entity\Survey;
use App\Domain\Entity\SurveyStat;
use App\Domain\Repository\SurveyStatItemRepositoryInterface;
use App\Domain\Repository\SurveyStatRepositoryInterface;
use App\Domain\Service\Db\TransactionManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;
use Throwable;

class SurveyStatService
{
    private LoggerInterface $logger;

    public function __construct(
        LoggerInterface $logger,
        private SurveyStatRepositoryInterface $surveyStatRepository,
        private SurveyStatItemRepositoryInterface $surveyStatItemRepository,
        private TransactionManagerInterface $transactionManager,
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

    /**
     * @param Survey $survey
     * @param bool $transaction
     *
     * @return void
     * @throws Throwable
     */
    public function refreshStat(Survey $survey, bool $transaction = true): void
    {
        $this->refreshStats([$survey->getId()], $transaction);
    }

    /**
     * @param Uuid[] $surveyIds
     * @param bool $transaction
     *
     * @return int
     * @throws Throwable
     */
    public function refreshStats(array|null $surveyIds = null, bool $transaction = true): int
    {
        if ($transaction) {
            $this->transactionManager->beginTransaction();
        }
        try {
            $stats = $this
                ->surveyStatRepository
                ->findStatFromSurveys($surveyIds);
            $items = $this
                ->surveyStatItemRepository
                ->findStatFromSurveys($surveyIds);

            // Заменяем данные в базе
            $this
                ->surveyStatRepository
                ->createOrUpdate($stats);
            $this
                ->surveyStatItemRepository
                ->createOrUpdate($items);
            if ($transaction) {
                $this->transactionManager->commit();
            }
            return count($stats);
        } catch (Throwable $e) {
            if ($transaction) {
                $this->transactionManager->rollback();
            }
            throw $e;
        }
    }
}
