<?php

namespace App\Infrastructure\Repository;

use App\Domain\DataProvider\DataProviderInterface;
use App\Domain\DataProvider\DataSort;
use App\Domain\DataProvider\LimitOffset;
use App\Domain\DataProvider\SortColumn;
use App\Domain\Dto\GetAllGroupsDto;
use App\Domain\Dto\GetAllUserDataDto;
use App\Domain\Entity\Group;
use App\Domain\Entity\UserData;
use App\Domain\Entity\UserDataGroup;
use App\Domain\Repository\GroupRepositoryInterface;
use App\Domain\Repository\UserDataRepositoryInterface;
use App\Infrastructure\Db\Expr\FullNameExpr;
use App\Infrastructure\Db\Expr\ILikeExpr;
use App\Infrastructure\Repository\Common\AbstractRepository;
use Qstart\Db\QueryBuilder\DML\Expression\Expr;
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
                    $dto->getSortBy(),
                    $dto->getSortType()->getPhpSort()
                ),
            ]),
        );
    }
}
