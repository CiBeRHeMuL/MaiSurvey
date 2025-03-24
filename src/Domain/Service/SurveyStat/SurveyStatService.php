<?php

namespace App\Domain\Service\SurveyStat;

use App\Domain\DataProvider\DataProviderInterface;
use App\Domain\Dto\Survey\GetSurveysDto;
use App\Domain\Entity\Survey;
use App\Domain\Entity\SurveyStat;
use App\Domain\Enum\ValidationErrorSlugEnum;
use App\Domain\Exception\ValidationException;
use App\Domain\Repository\SurveyStatItemRepositoryInterface;
use App\Domain\Repository\SurveyStatRepositoryInterface;
use App\Domain\Service\Db\TransactionManagerInterface;
use App\Domain\Service\Survey\SurveyService;
use App\Domain\Validation\ValidationError;
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
     * @param Survey[]|null $surveys
     * @param bool $transaction
     * @param bool $force обновить все опросы принудительно
     *
     * @return int
     * @throws Throwable
     */
    public function refreshStats(array|null $surveys, bool $transaction = true, bool $force = false): int
    {
        if ($surveys === []) {
            return 0;
        }
        if ($transaction) {
            $this->transactionManager->beginTransaction();
        }
        try {
            if ($force === false) {
                $surveys = $surveys !== null
                    ? array_filter(
                        $surveys,
                        fn(Survey $s) => $s->isActual(),
                    )
                    : null;
            }
            $surveyIds = $surveys !== null ? array_map(fn(Survey $s) => $s->getId(), $surveys) : null;
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

    /**
     * @param GetSurveysDto $dto
     * @return DataProviderInterface<SurveyStat>
     */
    public function getForSurveys(GetSurveysDto $dto): DataProviderInterface
    {
        if ($dto->getTitle() !== null && mb_strlen($dto->getTitle()) < 3) {
            throw ValidationException::new([
                new ValidationError(
                    'title',
                    ValidationErrorSlugEnum::WrongField->getSlug(),
                    'Для поиска по названию название должно быть длиннее 3 символов',
                ),
            ]);
        }
        if (!in_array($dto->getSortBy(), SurveyService::GET_ALL_SORT, true)) {
            throw ValidationException::new([
                new ValidationError(
                    'sort_by',
                    ValidationErrorSlugEnum::WrongField->getSlug(),
                    sprintf('Сортировка доступна по полям: %s', implode(', ', SurveyService::GET_ALL_SORT)),
                ),
            ]);
        }

        return $this
            ->surveyStatRepository
            ->findAll($dto);
    }
}
