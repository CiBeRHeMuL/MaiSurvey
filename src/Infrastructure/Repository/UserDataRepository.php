<?php

namespace App\Infrastructure\Repository;

use App\Domain\DataProvider\DataProviderInterface;
use App\Domain\DataProvider\DataSort;
use App\Domain\DataProvider\LimitOffset;
use App\Domain\DataProvider\SortColumn;
use App\Domain\Dto\UserData\GetAllUserDataDto;
use App\Domain\Entity\User;
use App\Domain\Entity\UserData;
use App\Domain\Entity\UserDataGroup;
use App\Domain\Repository\UserDataRepositoryInterface;
use App\Infrastructure\Db\Expr\FullNameExpr;
use App\Infrastructure\Db\Expr\ILikeExpr;
use App\Infrastructure\Repository\Common\AbstractRepository;
use Qstart\Db\QueryBuilder\DML\Expression\Expr;
use Qstart\Db\QueryBuilder\DML\Expression\InExpr;
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
                'ud.for_role' => $dto->getForRole()?->value,
            ]);
        if ($dto->withGroup() !== null) {
            if ($dto->withGroup()) {
                $q
                    ->andWhere(new Expr('udg IS NOT NULL'));
            } else {
                $q
                    ->andWhere(new Expr('udg IS NULL'));
            }
        }
        if ($dto->withUser() !== null) {
            if ($dto->withUser()) {
                $q
                    ->andWhere(new Expr('ud.user_id IS NOT NULL'));
            } else {
                $q
                    ->andWhere(new Expr('ud.user_id IS NULL'));
            }
        }
        if ($dto->getName() !== null) {
            $q
                ->andWhere(new ILikeExpr(new FullNameExpr('ud'), $dto->getName()));
        }
        return $this->findWithProvider(
            $q,
            UserData::class,
            ['group', 'group.group'],
            limit: new LimitOffset(
                $dto->getLimit(),
                $dto->getOffset(),
            ),
            sort: new DataSort([
                new SortColumn(
                    match ($dto->getSortBy()) {
                        'name' => 'name',
                        default => "ud.{$dto->getSortBy()}",
                    },
                    $dto->getSortBy(),
                    $dto->getSortType()->getPhpSort(),
                ),
            ]),
        );
    }

    public function findEmailsByNames(array $names): array
    {
        $q = Query::select()
            ->select(['u.email'])
            ->distinct(true)
            ->from(['ud' => $this->getClassTable(UserData::class)])
            ->innerJoin(
                ['u' => $this->getClassTable(User::class)],
                'u.id = ud.user_id',
            )
            ->where(new InExpr(new FullNameExpr('ud'), $names));
        return $this->findColumnByQuery($q);
    }

    public function findAllByIdsWithIdsOrder(array $ids): array
    {
        $q = Query::select()
            ->from(['t' => $this->getClassTable(UserData::class)])
            ->where(['id' => $ids])
            ->orderBy([
                new Expr(
                    'array_position(ARRAY[' . implode(', ', array_fill(0, count($ids), '?')) . ']::text[], id::text) ASC',
                    $ids,
                ),
            ]);
        return $this->findAllByQuery($q, UserData::class);
    }
}
