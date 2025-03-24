<?php

namespace App\Infrastructure\Repository;

use App\Domain\DataProvider\ArrayDataProvider;
use App\Domain\DataProvider\DataProviderInterface;
use App\Domain\DataProvider\DataSort;
use App\Domain\DataProvider\LimitOffset;
use App\Domain\DataProvider\SortColumn;
use App\Domain\Dto\Survey\GetSurveysDto;
use App\Domain\Entity\CompletedSurvey;
use App\Domain\Entity\MySurvey;
use App\Domain\Entity\Subject;
use App\Domain\Entity\Survey;
use App\Domain\Entity\SurveyStat;
use App\Domain\Enum\SurveyStatusEnum;
use App\Domain\Repository\SurveyStatRepositoryInterface;
use App\Infrastructure\Db\Expr\ActualSurveyExpr;
use App\Infrastructure\Db\Expr\CoalesceFunc;
use App\Infrastructure\Db\Expr\ILikeExpr;
use Qstart\Db\QueryBuilder\DML\Expression\Expr;
use Qstart\Db\QueryBuilder\DML\Expression\InExpr;
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
     * @param GetSurveysDto $dto
     * @return DataProviderInterface<SurveyStat>
     */
    public function findAll(GetSurveysDto $dto): DataProviderInterface
    {
        if ($dto->getStatuses() === [] || $dto->getSubjectIds() === []) {
            return new ArrayDataProvider([]);
        }
        $q = Query::select()
            ->select([
                's.*',
                'name' => 'ss.name',
            ])
            ->from(['s' => $this->getClassTable(Survey::class)])
            ->innerJoin(
                ['ss' => $this->getClassTable(Subject::class)],
                'ss.id = s.subject_id',
            );
        if ($dto->getTitle() !== null) {
            $q->andWhere([
                'OR',
                new ILikeExpr(new Expr('s.title'), $dto->getTitle()),
                new ILikeExpr(new Expr('ss.name'), $dto->getTitle()),
            ]);
        }
        if ($dto->getSubjectIds() !== null) {
            $q->andWhere(
                new InExpr(
                    's.subject_id',
                    array_map(
                        fn(Uuid $uuid) => $uuid->toRfc4122(),
                        $dto->getSubjectIds(),
                    ),
                ),
            );
        }
        if ($dto->getActual() !== null) {
            $q->andWhere(
                new ActualSurveyExpr('s', $dto->getActual() === false),
            );
        }
        if ($dto->getStatuses() !== null) {
            $q->andWhere([
                's.status' => array_map(
                    fn(SurveyStatusEnum $e) => $e->value,
                    $dto->getStatuses(),
                ),
            ]);
        }

        $q = Query::select()
            ->select(['t.*'])
            ->from(['t' => $this->getClassTable(SurveyStat::class)])
            ->innerJoin(
                ['s' => $q],
                's.id = t.id',
            );

        return $this->findWithLazyBatchedProvider(
            $q,
            SurveyStat::class,
            ['survey', 'items', 'survey.subject', 'items.item'],
            new LimitOffset(
                $dto->getLimit(),
                $dto->getOffset(),
            ),
            new DataSort([
                new SortColumn(
                    "s.{$dto->getSortBy()}",
                    $dto->getSortBy(),
                    $dto->getSortType()->getPhpSort(),
                ),
            ]),
        );
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
