<?php

namespace App\Infrastructure\Repository;

use App\Domain\DataProvider\DataProviderInterface;
use App\Domain\DataProvider\DataSort;
use App\Domain\DataProvider\LimitOffset;
use App\Domain\DataProvider\SortColumn;
use App\Domain\Dto\Subject\GetAllSubjectsDto;
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
                    $dto->getSortBy(),
                    $dto->getSortType()->getPhpSort(),
                ),
            ]),
        );
    }

    public function findByName(string $name): Subject|null
    {
        $q = Query::select()
            ->select(['*'])
            ->from($this->getClassTable(Subject::class))
            ->where(
                new Expr(
                    'lower(name) = :name',
                    ['name' => strtolower($name)],
                ),
            );
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

    public function findByNames(array $groupNames): Iterator
    {
        $q = Query::select()
            ->select(['*'])
            ->from($this->getClassTable(Subject::class))
            ->where(
                new InExpr(
                    'lower(name)',
                    array_map(strtolower(...), $groupNames),
                )
            );
        yield from $this->findAllByQuery($q, Subject::class);
    }
}
