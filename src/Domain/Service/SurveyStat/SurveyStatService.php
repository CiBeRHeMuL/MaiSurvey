<?php

namespace App\Domain\Service\SurveyStat;

use App\Domain\Entity\Survey;
use App\Domain\Entity\SurveyStat;
use App\Domain\Repository\SurveyStatItemRepositoryInterface;
use App\Domain\Repository\SurveyStatRepositoryInterface;
use App\Domain\Service\Db\TransactionManagerInterface;
use App\Domain\Service\Survey\SurveyService;
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
        private SurveyService $surveyService,
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
     * @param bool $force обновить все опросы принудительно
     *
     * @return void
     * @throws Throwable
     */
    public function refreshStat(Survey $survey, bool $transaction = true, bool $force = false): void
    {
        $this->refreshStats([$survey], $transaction, $force);
    }

    /**
     * @param Survey[] $surveys
     * @param bool $transaction
     * @param bool $force обновить все опросы принудительно
     *
     * @return int
     * @throws Throwable
     */
    public function refreshStats(array $surveys, bool $transaction = true, bool $force = false): int
    {
        if ($surveys === []) {
            return 0;
        }
        if ($transaction) {
            $this->transactionManager->beginTransaction();
        }
        try {
            if ($force === false) {
                $surveys = array_filter(
                    $surveys,
                    fn(Survey $s) => $s->isActual(),
                );
            }
            $surveyIds = array_map(fn(Survey $s) => $s->getId(), $surveys);
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
