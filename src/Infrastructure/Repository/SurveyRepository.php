<?php

namespace App\Infrastructure\Repository;

use App\Domain\DataProvider\DataProviderInterface;
use App\Domain\DataProvider\DataSort;
use App\Domain\DataProvider\LimitOffset;
use App\Domain\DataProvider\SortColumn;
use App\Domain\Dto\Survey\GetMySurveyByIdDto;
use App\Domain\Dto\Survey\GetMySurveysDto;
use App\Domain\Entity\MySurvey;
use App\Domain\Entity\Subject;
use App\Domain\Entity\Survey;
use App\Domain\Entity\User;
use App\Domain\Repository\SurveyRepositoryInterface;
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
            ['survey', 'survey.subject', 'user', 'user.data', 'myItems', 'myItems.surveyItem'],
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
            ['survey', 'survey.subject', 'user', 'user.data', 'myItems', 'myItems.surveyItem'],
        );
    }

    public function findById(Uuid $id): Survey|null
    {
        $q = Query::select()
            ->from(['t' => $this->getClassTable(Survey::class)])
            ->where(['id' => $id->toRfc4122()]);
        return $this->findOneByQuery($q, Survey::class, ['items', 'subject', 'subject.semester']);
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
