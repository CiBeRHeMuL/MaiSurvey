<?php

namespace App\Domain\Repository;

use App\Domain\Entity\SurveyStatItem;
use Symfony\Component\Uid\Uuid;

interface SurveyStatItemRepositoryInterface extends Common\RepositoryInterface
{
    /**
     * Получить статистику динамически (не из целевой таблицы, а из свежих данных)
     *
     * @param Uuid $surveyId
     *
     * @return SurveyStatItem[]
     */
    public function findStatFromSurvey(Uuid $surveyId): array;

    /**
     * Создает или заменяет записи в базе
     *
     * @param SurveyStatItem[] $items
     *
     * @return void
     */
    public function createOrUpdate(array $items): void;

    /**
     * @param Uuid[] $surveyIds
     *
     * @return SurveyStatItem[]
     */
    public function findStatFromSurveys(array|null $surveyIds = null): array;
}
