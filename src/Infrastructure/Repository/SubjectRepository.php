<?php

namespace App\Infrastructure\Repository;

use App\Domain\DataProvider\DataProviderInterface;
use App\Domain\DataProvider\DataSort;
use App\Domain\DataProvider\LimitOffset;
use App\Domain\DataProvider\SortColumn;
use App\Domain\Dto\Subject\GetAllSubjectsDto;
use App\Domain\Dto\Subject\GetByRawIndexDto;
use App\Domain\Entity\Semester;
use App\Domain\Entity\Subject;
use App\Domain\Repository\SubjectRepositoryInterface;
use App\Infrastructure\Db\Expr\ILikeExpr;
use App\Infrastructure\Repository\Common\AbstractRepository;
use Iterator;
use Qstart\Db\QueryBuilder\DML\Expression\Expr;
use Qstart\Db\QueryBuilder\DML\Expression\InExpr;
use Qstart\Db\QueryBuilder\Query;
use Symfony\Component\Uid\Uuid;

class SubjectRepository extends AbstractRepository implements SubjectRepositoryInterface
{
    public function findAll(GetAllSubjectsDto $dto): DataProviderInterface
    {
        $q = Query::select()
            ->select(['s.*'])
            ->from(['s' => $this->getClassTable(Subject::class)]);
        if ($dto->getName() !== null) {
            $q->andWhere(new ILikeExpr(new Expr('s.name'), $dto->getName()));
        }
        return $this->findWithProvider(
            $q,
            Subject::class,
            limit: new LimitOffset(
                $dto->getLimit(),
                $dto->getOffset(),
            ),
            sort: new DataSort([
                new SortColumn(
                    "s.{$dto->getSortBy()}",
                    $dto->getSortBy(),
                    $dto->getSortType()->getPhpSort(),
                ),
            ]),
        );
    }

    public function findByIndex(string $name, Uuid $semesterId): Subject|null
    {
        $q = Query::select()
            ->select(['*'])
            ->from($this->getClassTable(Subject::class))
            ->where(
                new Expr(
                    'lower(name) = :name',
                    ['name' => mb_strtolower($name)],
                ),
            )
            ->andWhere(['semester_id' => $semesterId->toRfc4122()]);
        return $this->findOneByQuery($q, Subject::class);
    }

    public function findById(Uuid $id): Subject|null
    {
        $q = Query::select()
            ->select(['*'])
            ->from($this->getClassTable(Subject::class))
            ->where(['id' => $id->toRfc4122()]);
        return $this->findOneByQuery($q, Subject::class);
    }

    public function findByRawIndexes(array $indexes): Iterator
    {
        $q = Query::select()
            ->select(['s.*'])
            ->from(['s' => $this->getClassTable(Subject::class)])
            ->innerJoin(
                ['sem' => $this->getClassTable(Semester::class)],
                'sem.id = s.semester_id',
            )
            ->where(
                new InExpr(
                    ['lower(s.name)', 'sem.year', 'sem.spring'],
                    array_map(
                        fn(GetByRawIndexDto $i) => [
                            mb_strtolower($i->getName()),
                            $i->getSemesterDto()->getYear(),
                            $i->getSemesterDto()->isSpring(),
                        ],
                        $indexes,
                    ),
                )
            );
        yield from $this->findAllByQuery($q, Subject::class);
    }
}
