<?php

namespace App\Infrastructure\Repository;

use App\Domain\DataProvider\DataProviderInterface;
use App\Domain\DataProvider\DataSort;
use App\Domain\DataProvider\LimitOffset;
use App\Domain\DataProvider\SortColumn;
use App\Domain\Dto\SurveyTemplate\GetAllSurveyTemplatesDto;
use App\Domain\Entity\SurveyTemplate;
use App\Domain\Repository\SurveyTemplateRepositoryInterface;
use App\Infrastructure\Db\Expr\ILikeExpr;
use Qstart\Db\QueryBuilder\DML\Expression\Expr;
use Qstart\Db\QueryBuilder\Query;
use Symfony\Component\Uid\Uuid;

class SurveyTemplateRepository extends Common\AbstractRepository implements SurveyTemplateRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function findAll(GetAllSurveyTemplatesDto $dto): DataProviderInterface
    {
        $q = Query::select()
            ->from(['t' => $this->getClassTable(SurveyTemplate::class)]);
        if ($dto->getName() !== null) {
            $q->andWhere(new ILikeExpr(new Expr('t.name'), $dto->getName()));
        }
        return $this->findWithLazyBatchedProvider(
            $q,
            SurveyTemplate::class,
            ['items'],
            new LimitOffset($dto->getLimit(), $dto->getOffset()),
            new DataSort([
                new SortColumn(
                    $dto->getSortBy(),
                    $dto->getSortBy(),
                    $dto->getSortType()->getPhpSort(),
                ),
            ]),
        );
    }

    public function findById(Uuid $id): SurveyTemplate|null
    {
        $q = Query::select()
            ->from($this->getClassTable(SurveyTemplate::class))
            ->where(['id' => $id->toRfc4122()]);
        return $this->findOneByQuery($q, SurveyTemplate::class, ['items']);
    }
}
