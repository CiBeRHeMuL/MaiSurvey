<?php

namespace App\Infrastructure\Repository;

use App\Domain\Entity\CompletedSurvey;
use App\Domain\Entity\MySurvey;
use App\Domain\Entity\Survey;
use App\Domain\Entity\SurveyStat;
use App\Domain\Repository\SurveyStatRepositoryInterface;
use App\Infrastructure\Db\Expr\CoalesceFunc;
use Qstart\Db\QueryBuilder\DML\Expression\Expr;
use Qstart\Db\QueryBuilder\DML\Query\SelectQuery;
use Qstart\Db\QueryBuilder\Query;
use Symfony\Component\Uid\Uuid;

class SurveyStatRepository extends Common\AbstractRepository implements SurveyStatRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function findForSurvey(Uuid $surveyId): SurveyStat|null
    {
        $q = Query::select()
            ->from(['t' => $this->getClassTable(SurveyStat::class)])
            ->where([
                'id' => $surveyId->toRfc4122(),
            ]);
        return $this
            ->findOneByQuery(
                $q,
                SurveyStat::class,
                ['items', 'items.item', 'survey'],
            );
    }

    /**
     * @inheritDoc
     */
    public function findStatFromSurvey(Uuid $surveyId): SurveyStat
    {
        $q = $this->getFromSurveysQuery([$surveyId]);
        return $this
            ->findOneByQuery(
                $q,
                SurveyStat::class,
            );
    }

    public function findStatFromSurveys(array|null $surveyIds = null): array
    {
        $q = $this->getFromSurveysQuery($surveyIds);
        return $this
            ->findAllByQuery(
                $q,
                SurveyStat::class,
            );
    }

    public function createOrUpdate(array $stats): void
    {
        if ($stats === []) {
            return;
        }
        $this->executeQuery(
            Query::delete()
                ->from($this->getClassTable(SurveyStat::class))
                ->where(['id' => array_map(fn(SurveyStat $e) => $e->getId()->toRfc4122(), $stats)]),
        );
        $this->createMulti($stats);
    }

    /**
     * @param Uuid[]|null $surveyIds
     *
     * @return SelectQuery
     */
    private function getFromSurveysQuery(array|null $surveyIds = null): SelectQuery
    {
        $surveyIds = $surveyIds !== null
            ? array_map(fn(Uuid $i) => $i->toRfc4122(), $surveyIds)
            : null;
        return Query::select()
            ->select([
                's.id',
                'available_count' => new CoalesceFunc(new Expr('ms.count'), 0),
                'completed_count' => new CoalesceFunc(new Expr('cs.count'), 0),
            ])
            ->from(['s' => $this->getClassTable(Survey::class)])
            ->leftJoin(
                [
                    'cs' => Query::select()
                        ->select([
                            'count' => new Expr('count(*)'),
                            'survey_id',
                        ])
                        ->from($this->getClassTable(CompletedSurvey::class))
                        ->groupBy(['survey_id']),
                ],
                'cs.survey_id = s.id',
            )
            ->leftJoin(
                [
                    'ms' => Query::select()
                        ->select([
                            'count' => new Expr('count(*)'),
                            'id',
                        ])
                        ->from($this->getClassTable(MySurvey::class))
                        ->groupBy(['id']),
                ],
                'ms.id = s.id',
            )
            ->filterWhere(['s.id' => $surveyIds]);
    }
}
