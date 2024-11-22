<?php

namespace App\Infrastructure\Repository;

use App\Domain\DataProvider\DataProviderInterface;
use App\Domain\DataProvider\DataSort;
use App\Domain\DataProvider\LimitOffset;
use App\Domain\DataProvider\SortColumn;
use App\Domain\Dto\GetAllUserDataDto;
use App\Domain\Entity\Group;
use App\Domain\Entity\UserData;
use App\Domain\Entity\UserDataGroup;
use App\Domain\Repository\UserDataRepositoryInterface;
use App\Infrastructure\Db\Expr\FullNameExpr;
use App\Infrastructure\Db\Expr\ILikeExpr;
use App\Infrastructure\Repository\Common\AbstractRepository;
use Doctrine\ORM\Query\Expr\OrderBy;
use Qstart\Db\QueryBuilder\DML\Expression\Expr;
use Qstart\Db\QueryBuilder\Query;
use Symfony\Component\Uid\Uuid;

class UserDataRepository extends AbstractRepository implements UserDataRepositoryInterface
{
    public function findById(Uuid $id): UserData|null
    {
        return $this
            ->getEntityManager()
            ->getRepository(UserData::class)
            ->find($id);
    }

    public function findByUserId(Uuid $userId): UserData|null
    {
        return $this
            ->getEntityManager()
            ->getRepository(UserData::class)
            ->findOneBy(['userId' => $userId]);
    }

    public function findAll(GetAllUserDataDto $dto): DataProviderInterface
    {
        $q = Query::select()
            ->select([
                'ud.*',
                'name' => new FullNameExpr('ud'),
            ])
            ->from(['ud' => $this->getClassTable(UserData::class)])
            ->leftJoin(
                ['udg' => $this->getClassTable(UserDataGroup::class)],
                'udg.user_data_id = ud.id',
            )
            ->filterWhere([
                'udg.group_id' => $dto->getGroupIds() !== null
                    ? array_map(
                        fn(Uuid $id) => $id->toRfc4122(),
                        $dto->getGroupIds(),
                    )
                    : null,
            ]);
        if ($dto->isOnlyWithGroup()) {
            $q
                ->andWhere(new Expr('udg IS NOT NULL'));
        }
        if ($dto->getName() !== null) {
            $q
                ->andWhere(new ILikeExpr(new FullNameExpr('ud'), $dto->getName()));
        }
        return $this->findWithProvider(
            $q,
            UserData::class,
            ['group'],
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
