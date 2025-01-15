<?php

namespace App\Infrastructure\Repository;

use App\Domain\DataProvider\DataProviderInterface;
use App\Domain\DataProvider\DataSort;
use App\Domain\DataProvider\DataSortInterface;
use App\Domain\DataProvider\LimitOffset;
use App\Domain\DataProvider\SortColumn;
use App\Domain\Dto\User\GetAllUsersDto;
use App\Domain\Entity\User;
use App\Domain\Entity\UserData;
use App\Domain\Entity\UserDataGroup;
use App\Domain\Enum\RoleEnum;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\ValueObject\Email;
use App\Infrastructure\Db\Expr\FullNameExpr;
use App\Infrastructure\Db\Expr\ILikeExpr;
use App\Infrastructure\Repository\Common\AbstractRepository;
use Qstart\Db\QueryBuilder\DML\Expression\Expr;
use Qstart\Db\QueryBuilder\DML\Expression\InExpr;
use Qstart\Db\QueryBuilder\Query;
use Symfony\Component\Uid\Uuid;

class UserRepository extends AbstractRepository implements UserRepositoryInterface
{
    public function findById(Uuid $uuid): User|null
    {
        return $this
            ->getEntityManager()
            ->getRepository(User::class)
            ->find($uuid);
    }

    public function findByEmail(Email $email): User|null
    {
        $q = Query::select()
            ->from($this->getClassTable(User::class))
            ->where(
                new Expr(
                    'lower(email) = :email',
                    ['email' => strtolower($email->getEmail())],
                ),
            );
        return $this->findOneByQuery($q, User::class);
    }

    public function findAll(GetAllUsersDto $dto): DataProviderInterface
    {
        $q = Query::select()
            ->select(['u.*', 'name' => new FullNameExpr('ud')])
            ->from(['u' => $this->getClassTable(User::class)])
            ->leftJoin(
                ['ud' => $this->getClassTable(UserData::class)],
                'ud.user_id = u.id',
            )
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
        if ($dto->getRoles() !== null) {
            $roles = array_unique(
                array_map(
                    fn(RoleEnum $r) => $r->value,
                    $dto->getRoles(),
                ),
            );
            $roles = array_combine(
                array_map(fn(int $e) => ":r_$e", range(0, count($roles) - 1)),
                $roles,
            );

            $q
                ->andWhere(
                    new Expr(
                        'u.roles && ARRAY[' . implode(', ', array_keys($roles)) . ']::text[]',
                        $roles,
                    ),
                );
        }
        if ($dto->getName() !== null) {
            $q->andWhere(new ILikeExpr(new FullNameExpr('ud'), $dto->getName()));
        }
        if ($dto->getEmail() !== null) {
            $q->andWhere(new ILikeExpr('u.email', $dto->getEmail()));
        }
        if ($dto->getDeleted() !== null) {
            $q->andWhere(['u.deleted' => $dto->getDeleted()]);
        }
        if ($dto->getStatus() !== null) {
            $q->andWhere(['u.status' => $dto->getStatus()->value]);
        }
        if ($dto->getWithGroup() !== null) {
            if ($dto->getWithGroup()) {
                $q->andWhere(new Expr('udg IS NOT NULL'));
            } else {
                $q->andWhere(new Expr('udg IS NULL'));
            }
        }
        if ($dto->getCreatedFrom()) {
            $q->andWhere(new Expr('u.created_at >= :caf', ['caf' => $dto->getCreatedFrom()->format(DATE_RFC3339)]));
        }
        if ($dto->getCreatedTo()) {
            $q->andWhere(new Expr('u.created_at <= :cat', ['cat' => $dto->getCreatedTo()->format(DATE_RFC3339)]));
        }
        return $this
            ->findWithLazyBatchedProvider(
                $q,
                User::class,
                ['data', 'data.group', 'data.group.group'],
                new LimitOffset(
                    $dto->getLimit(),
                    $dto->getOffset(),
                ),
                new DataSort([
                    new SortColumn(
                        match ($dto->getSortBy()) {
                            'name' => (new FullNameExpr('ud'))->getExpression(),
                            default => "u.{$dto->getSortBy()}",
                        },
                        $dto->getSortBy(),
                        $dto->getSortType()->getPhpSort(),
                    ),
                ]),
            );
    }

    public function findAllByEmails(array $emails): array
    {
        $q = Query::select()
            ->select(['*'])
            ->from($this->getClassTable(User::class))
            ->where(
                new InExpr(
                    'email',
                    array_map(
                        fn(Email $e) => strtolower($e->getEmail()),
                        $emails,
                    ),
                ),
            );
        return $this
            ->findAllByQuery(
                $q,
                User::class,
                ['data', 'data.group', 'data.group.group'],
            );
    }

    public function findLastN(int $count, DataSortInterface $sort): DataProviderInterface
    {
        $q = Query::select()
            ->from($this->getClassTable(User::class));
        return $this->findWithLazyBatchedProvider(
            $q,
            User::class,
            ['data', 'data.group', 'data.group.group'],
            new LimitOffset($count, 0),
            $sort,
        );
    }
}
