<?php

namespace App\Infrastructure\Repository;

use App\Domain\DataProvider\ArrayDataProvider;
use App\Domain\DataProvider\DataProviderInterface;
use App\Domain\DataProvider\DataSort;
use App\Domain\DataProvider\LimitOffset;
use App\Domain\DataProvider\SortColumn;
use App\Domain\Dto\Survey\GetMySurveyByIdDto;
use App\Domain\Dto\Survey\GetMySurveysDto;
use App\Domain\Dto\Survey\GetSurveysDto;
use App\Domain\Entity\MySurvey;
use App\Domain\Entity\Subject;
use App\Domain\Entity\Survey;
use App\Domain\Entity\User;
use App\Domain\Enum\SurveyStatusEnum;
use App\Domain\Repository\SurveyRepositoryInterface;
use App\Infrastructure\Db\Expr\ActualSurveyExpr;
use App\Infrastructure\Db\Expr\ILikeExpr;
use Qstart\Db\QueryBuilder\DML\Expression\Expr;
use Qstart\Db\QueryBuilder\DML\Expression\InExpr;
use Qstart\Db\QueryBuilder\DML\Query\SelectQuery;
use Qstart\Db\QueryBuilder\Query;
use Symfony\Component\Uid\Uuid;

class SurveyRepository extends Common\AbstractRepository implements SurveyRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function findMy(User $user, GetMySurveysDto $dto): DataProviderInterface
    {
        $q = $this
            ->getMyQuery($user)
            ->andFilterWhere([
                'ms.completed' => $dto->getCompleted(),
                'ms.actual' => $dto->getActual(),
            ]);
        if ($dto->getSubjectIds() !== null) {
            $q->andWhere(new InExpr(
                'ms.subject_id',
                array_map(
                    fn(Uuid $uuid) => $uuid->toRfc4122(),
                    $dto->getSubjectIds(),
                ),
            ));
        }
        return $this->findWithLazyBatchedProvider(
            $q,
            MySurvey::class,
            [
                'survey',
                'survey.subject',
                'user',
                'user.data',
                'myItems',
                'myItems.surveyItem',
                'survey.subject.semester',
                'myItems.teacherSubject.teacher.data.group',
            ],
            new LimitOffset(
                $dto->getLimit(),
                $dto->getOffset(),
            ),
            new DataSort([
                new SortColumn(
                    match ($dto->getSortBy()) {
                        'created_at' => 's.created_at',
                        'name' => 'ss.name',
                        default => "ms.{$dto->getSortBy()}",
                    },
                    $dto->getSortBy(),
                    $dto->getSortType()->getPhpSort(),
                ),
            ]),
        );
    }

    public function findMyById(User $user, GetMySurveyByIdDto $dto): MySurvey|null
    {
        $q = $this->getMyQuery($user)
            ->andWhere(['ms.id' => $dto->getId()->toRfc4122()])
            ->andFilterWhere([
                'ms.completed' => $dto->getCompleted(),
                'ms.actual' => $dto->getActual(),
            ]);
        return $this->findOneByQuery(
            $q,
            MySurvey::class,
            [
                'survey',
                'survey.subject',
                'user',
                'user.data',
                'myItems',
                'myItems.surveyItem',
                'survey.subject.semester',
                'myItems.teacherSubject.teacher.data.group',
            ],
        );
    }

    public function findById(Uuid $id): Survey|null
    {
        $q = Query::select()
            ->from(['t' => $this->getClassTable(Survey::class)])
            ->where(['id' => $id->toRfc4122()]);
        return $this->findOneByQuery($q, Survey::class, ['items', 'subject', 'subject.semester']);
    }

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
            $q->andWhere(new InExpr(
                's.subject_id',
                array_map(
                    fn(Uuid $uuid) => $uuid->toRfc4122(),
                    $dto->getSubjectIds(),
                ),
            ));
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
        return $this->findWithLazyBatchedProvider(
            $q,
            Survey::class,
            ['subject', 'subject.semester'],
            new LimitOffset(
                $dto->getLimit(),
                $dto->getOffset(),
            ),
            new DataSort([
                new SortColumn(
                    match ($dto->getSortBy()) {
                        'name' => 'ss.name',
                        default => "s.{$dto->getSortBy()}",
                    },
                    $dto->getSortBy(),
                    $dto->getSortType()->getPhpSort(),
                ),
            ]),
        );
    }

    public function findByIds(array $ids, ?bool $actual = null): array
    {
        $q = Query::select()
            ->from(['t' => $this->getClassTable(Survey::class)])
            ->where(['id' => array_map(fn(Uuid $id) => $id->toRfc4122(), $ids)]);
        if ($actual !== null) {
            $q->andWhere(
                new ActualSurveyExpr('t', $actual === false),
            );
        }
        return $this
            ->findAllByQuery(
                $q,
                Survey::class,
                ['items', 'subject', 'subject.semester'],
            );
    }

    private function getMyQuery(User $user): SelectQuery
    {
        return Query::select()
            ->select([
                'ms.*',
                'name' => 'ss.name',
            ])
            ->from(['ms' => $this->getClassTable(MySurvey::class)])
            ->innerJoin(
                ['s' => $this->getClassTable(Survey::class)],
                's.id = ms.id',
            )
            ->innerJoin(
                ['ss' => $this->getClassTable(Subject::class)],
                'ss.id = s.subject_id',
            )
            ->where([
                'ms.user_id' => $user->getId()->toRfc4122(),
            ]);
    }
}
