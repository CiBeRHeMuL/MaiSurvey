<?php

namespace App\Infrastructure\Repository;

use App\Domain\DataProvider\DataProviderInterface;
use App\Domain\DataProvider\DataSort;
use App\Domain\DataProvider\LimitOffset;
use App\Domain\DataProvider\SortColumn;
use App\Domain\Dto\Group\GetAllGroupsDto;
use App\Domain\Entity\Group;
use App\Domain\Repository\GroupRepositoryInterface;
use App\Infrastructure\Db\Expr\ILikeExpr;
use App\Infrastructure\Repository\Common\AbstractRepository;
use Qstart\Db\QueryBuilder\DML\Expression\Expr;
use Qstart\Db\QueryBuilder\DML\Expression\InExpr;
use Qstart\Db\QueryBuilder\Query;
use Symfony\Component\Uid\Uuid;

class GroupRepository extends AbstractRepository implements GroupRepositoryInterface
{
    public function findAll(GetAllGroupsDto $dto): DataProviderInterface
    {
        $q = Query::select()
            ->select(['g.*'])
            ->from(['g' => $this->getClassTable(Group::class)]);
        if ($dto->getName() !== null) {
            $q
                ->andWhere(new ILikeExpr(new Expr('g.name'), $dto->getName()));
        }
        return $this->findWithProvider(
            $q,
            Group::class,
            limit: new LimitOffset(
                $dto->getLimit(),
                $dto->getOffset(),
            ),
            sort: new DataSort([
                new SortColumn(
                    "g.{$dto->getSortBy()}",
                    $dto->getSortBy(),
                    $dto->getSortType()->getPhpSort(),
                ),
            ]),
        );
    }

    public function findByName(string $name): Group|null
    {
        $q = Query::select()
            ->select(['*'])
            ->from($this->getClassTable(Group::class))
            ->where(
                new Expr(
                    'lower(name) = :name',
                    ['name' => mb_strtolower($name)],
                ),
            );
        return $this
            ->findOneByQuery($q, Group::class);
    }

    public function findById(Uuid $id): Group|null
    {
        $q = Query::select()
            ->select(['*'])
            ->from($this->getClassTable(Group::class))
            ->where(['id' => $id->toRfc4122()]);
        return $this
            ->findOneByQuery($q, Group::class);
    }

    public function findByNames(array $groupNames): array
    {
        $q = Query::select()
            ->select(['*'])
            ->from($this->getClassTable(Group::class))
            ->where(
                new InExpr(
                    'lower(name)',
                    array_map(mb_strtolower(...), $groupNames),
                )
            );
        return $this->findAllByQuery($q, Group::class);
    }
}
