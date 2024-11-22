<?php

namespace App\Infrastructure\Repository\Common;

use App\Domain\DataProvider\ArrayDataProvider;
use App\Domain\DataProvider\DataLimitInterface;
use App\Domain\DataProvider\DataProviderInterface;
use App\Domain\DataProvider\DataSort;
use App\Domain\DataProvider\DataSortInterface;
use App\Domain\DataProvider\LimitOffset;
use App\Domain\DataProvider\SortColumnInterface;
use App\Domain\Repository\Common\RepositoryInterface;
use Doctrine\DBAL\Result;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NativeQuery;
use Doctrine\ORM\Query\ResultSetMapping;
use Exception;
use Psr\Log\LoggerInterface;
use Qstart\Db\QueryBuilder\DML\Query\QueryInterface;
use Qstart\Db\QueryBuilder\DML\Query\SelectQuery;
use Qstart\Db\QueryBuilder\Helper\DialectSQL;

abstract class AbstractRepository implements RepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        protected LoggerInterface $logger,
    ) {
    }

    public function getClassTable(string $class): string|null
    {
        return $this
            ->getEntityManager()
            ->getClassMetadata($class)
            ?->getTableName();
    }

    public function create(object $entity): bool
    {
        try {
            $this->entityManager->persist($entity);
            $this->entityManager->flush();
            return true;
        } catch (Exception $e) {
            $this->logger->error($e);
            return false;
        }
    }

    public function update(object $entity): bool
    {
        try {
            $this->entityManager->flush();
            return true;
        } catch (Exception $e) {
            $this->logger->error($e);
            return false;
        }
    }

    public function delete(object $entity): bool
    {
        try {
            $this->entityManager->remove($entity);
            return true;
        } catch (Exception $e) {
            $this->logger->error($e);
            return false;
        }
    }

    public function findOneByQuery(SelectQuery $query, string|null $entityClassName = null, array|null $relations = null)
    {
        $query->limit(1);
        if ($entityClassName) {
            $entities = $this->getEmNativeQuery($entityClassName, $query)->getOneOrNullResult();
            if ($entities && $relations) {
                return $this->populateRelations($entities, $relations);
            }
            return $entities;
        } else {
            return $this->executeQuery($query)->fetchAssociative();
        }
    }

    public function findAllByQuery(SelectQuery $query, string|null $entityClassName = null, array|null $relations = null): array
    {
        if ($entityClassName) {
            $entities = $this->getEmNativeQuery($entityClassName, $query)->getResult();
            if ($entities && $relations) {
                return $this->populateRelations($entities, $relations);
            }
            return $entities;
        }

        return $this->executeQuery($query)->fetchAllAssociative();
    }

    public function findWithProvider(
        SelectQuery $query,
        string|null $entityClassName = null,
        array|null $relations = null,
        DataLimitInterface|null $limit = null,
        DataSortInterface|null $sort = null,
    ): DataProviderInterface {
        $limit ??= new LimitOffset(null, 0);
        $sort ??= new DataSort([]);

        $items = [];
        $total = intval(
            $this->findOneByQuery(
                (clone $query)
                    ->select("count(*) AS count")
                    ->limit(null)
                    ->offset(null),
            )['count'] ?: 0,
        );

        if ($total > 0) {
            $clonedQuery = (clone $query)->limit($limit->getLimit())->offset($limit->getOffset());
            if ($sort->getSortColumns()) {
                $clonedQuery
                    ->orderBy(
                        array_merge(
                            ...array_map(
                            fn(SortColumnInterface $column) => [$column->getColumn() => $column->getSort()],
                            $sort->getSortColumns(),
                        ),
                        ),
                    );
            }

            $items = $this->findAllByQuery(
                $clonedQuery,
                $entityClassName,
                $relations,
            );
        }

        return new ArrayDataProvider(
            items: $items,
            total: $total,
            limit: $limit,
            sort: $sort,
        );
    }

    public function executeQuery(QueryInterface $query): Result
    {
        $qb = $query->getQueryBuilder()->setDialect(DialectSQL::POSTGRESQL);
        $expr = $qb->build();

        $connection = $this->getEntityManager()->getConnection();
        return $connection->executeQuery($expr->getExpression(), $expr->getParams());
    }

    /**
     * Подставляем релейшены в сущности.
     *
     * @template T of object
     * @param T[] $entities
     * @param string[] $relations
     *
     * @return T[]
     */
    protected function populateRelations(array $entities, array $relations): array
    {
        if (empty($entities) || empty($relations)) {
            return $entities;
        }

        $entityClass = $entities[0]::class;
        $metadata = $this->getEntityManager()->getClassMetadata($entityClass);

        // Получаем идентификаторы всех сущностей (поддержка нескольких primary key)
        $entityIds = [];
        foreach ($entities as $entity) {
            $identifier = $metadata->getIdentifierValues($entity);
            $entityIds[] = $identifier;
        }

        // Формируем DQL-запрос для подгрузки ассоциаций с учетом сложных ключей
        $qb = $this->getEntityManager()->createQueryBuilder()->select('e')->from("$entityClass", 'e');

        // Добавляем условия для каждого идентификатора
        $orX = $qb->expr()->orX();
        $i = 0;
        foreach ($entityIds as $id) {
            $andX = $qb->expr()->andX();
            foreach ($id as $field => $value) {
                $paramName = "prqb_$i";
                $i++;
                $andX->add($qb->expr()->eq("e.{$field}", ":{$paramName}"));
                $qb->setParameter($paramName, $value);
            }
            $orX->add($andX);
        }
        $qb->where($orX);

        $relations = array_combine(
            $relations,
            array_map(
                fn(string $r) => md5($r),
                $relations,
            ),
        );

        // Добавляем необходимые JOIN FETCH для ассоциаций
        foreach ($relations as $association => $hash) {
            $qb
                ->leftJoin("e.{$association}", $hash)
                ->addSelect($hash);
        }

        // Выполняем запрос и получаем сущности с подгруженными ассоциациями
        $results = $qb->getQuery()->getResult();

        // Заполняем исходный массив

        foreach ($results as $result) {
            $identifier = $metadata->getIdentifierValues($result);
            foreach ($entities as &$entity) {
                $enIdentifier = $metadata->getIdentifierValues($entity);
                if (serialize($enIdentifier) == serialize($identifier)) {
                    foreach ($relations as $association => $hash) {
                        $metadata->setFieldValue($entity, $association, $metadata->getFieldValue($result, $association));
                    }
                    break;
                }
            }
        }

        return $entities;
    }

    protected function getEntityManager(): EntityManagerInterface
    {
        return $this->entityManager;
    }

    protected function getEmNativeQuery(string $entityClassName, SelectQuery $query): NativeQuery
    {
        $qb = $query->getQueryBuilder()->setDialect(DialectSQL::POSTGRESQL);
        $expr = $qb->build();

        $rsm = new ResultSetMapping();

        $rsm->addEntityResult($entityClassName, $entityClassName);
        $mapping = $this->getEntityManager()->getClassMetadata($entityClassName);
        foreach ($mapping->fieldMappings as $fieldMapping) {
            $rsm->addFieldResult($entityClassName, $fieldMapping->columnName, $fieldMapping->fieldName);
        }

        $emQuery = $this->getEntityManager()->createNativeQuery($expr->getExpression(), $rsm);
        $emQuery->setParameters($expr->getParams());

        return $emQuery;
    }
}
