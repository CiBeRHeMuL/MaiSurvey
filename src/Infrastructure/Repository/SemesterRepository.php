<?php

namespace App\Infrastructure\Repository;

use App\Domain\DataProvider\DataProviderInterface;
use App\Domain\DataProvider\DataSort;
use App\Domain\DataProvider\LimitOffset;
use App\Domain\DataProvider\SortColumn;
use App\Domain\Dto\Semester\GetAllSemestersDto;
use App\Domain\Dto\Semester\GetSemesterByIndexDto;
use App\Domain\Entity\Semester;
use App\Domain\Repository\SemesterRepositoryInterface;
use Qstart\Db\QueryBuilder\DML\Expression\InExpr;
use Qstart\Db\QueryBuilder\Query;
use Symfony\Component\Uid\Uuid;

class SemesterRepository extends Common\AbstractRepository implements SemesterRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function findAllByIndexes(array $indexes): array
    {
        if ($indexes === []) {
            return [];
        }
        $q = Query::select()
            ->from($this->getClassTable(Semester::class))
            ->where(
                new InExpr(
                    ['year', 'spring'],
                    array_map(
                        fn(GetSemesterByIndexDto $dto) => ['year' => $dto->getYear(), 'spring' => $dto->isSpring()],
                        $indexes,
                    ),
                ),
            );
        return $this
            ->findAllByQuery(
                $q,
                Semester::class,
            );
    }

    public function findAll(GetAllSemestersDto $dto): DataProviderInterface
    {
        $q = Query::select()
            ->from($this->getClassTable(Semester::class));
        return $this
            ->findWithLazyBatchedProvider(
                $q,
                Semester::class,
                limit: new LimitOffset($dto->getLimit(), $dto->getOffset()),
                sort: new DataSort([
                    new SortColumn(
                        $dto->getSortBy(),
                        $dto->getSortBy(),
                        $dto->getSortType()->getPhpSort(),
                    ),
                ]),
            );
    }

    public function findById(Uuid $semesterId): Semester|null
    {
        $q = Query::select()
            ->from($this->getClassTable(Semester::class))
            ->where(['id' => $semesterId->toRfc4122()]);
        return $this
            ->findOneByQuery(
                $q,
                Semester::class,
            );
    }
}
