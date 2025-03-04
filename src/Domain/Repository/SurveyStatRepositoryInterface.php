<?php

namespace App\Domain\Repository;

use App\Domain\Entity\SurveyStat;
use App\Domain\Repository\Common\RepositoryInterface;
use Symfony\Component\Uid\Uuid;

interface SurveyStatRepositoryInterface extends RepositoryInterface
{
    /**
     * @param Uuid $surveyId
     *
     * @return SurveyStat|null
     */
    public function findForSurvey(Uuid $surveyId): SurveyStat|null;

    /**
     * Получить статистику динамически (не из целевой таблицы, а из свежих данных)
     * @param Uuid $surveyId
     *
     * @return SurveyStat
     */
    public function findStatFromSurvey(Uuid $surveyId): SurveyStat;

    /**
     * Получить статистику динамически (не из целевой таблицы, а из свежих данных)
     * @param Uuid[]|null $surveyIds
     *
     * @return SurveyStat[]
     */
    public function findStatFromSurveys(array|null $surveyIds = null): array;

    /**
     * Создает или заменяет запись в базе
     *
     * @param SurveyStat[] $stats
     *
     * @return void
     */
    public function createOrUpdate(array $stats): void;
}
