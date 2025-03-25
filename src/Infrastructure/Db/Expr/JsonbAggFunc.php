<?php

namespace App\Infrastructure\Db\Expr;

use Qstart\Db\QueryBuilder\DML\Expression\Expr;
use Qstart\Db\QueryBuilder\DML\Expression\ExprInterface;

class JsonbAggFunc extends AbstractFunc
{
    /**
     * @param bool|int|float|string|ExprInterface|null $input
     * @param ExprInterface|ExprInterface[]|null $orderBy
     * @param ExprInterface|null $filterWhere
     */
    public function __construct(
        null|bool|int|float|string|ExprInterface $input,
        ExprInterface|array|null $orderBy = null,
        private ExprInterface|null $filterWhere = null,
    ) {
        $oid = 'oid_' . spl_object_id($this);
        if (is_string($input)) {
            $input = new Expr(":{$oid}_input::text", [":{$oid}_input" => $input]);
        } elseif ($input === null) {
            $input = new Expr('NULL::text');
        } elseif (!($input instanceof ExprInterface)) {
            $input = new Expr(":{$oid}_input", [":{$oid}_input" => $input]);
        }
        if ($orderBy !== null) {
            $orderByParams = [];
            if ($orderBy instanceof ExprInterface) {
                $orderByParams = $orderBy->getParams();
                $orderBy = $orderBy->getExpression();
            } else {
                $orderByTexts = [];
                foreach ($orderBy as $expr) {
                    $orderByTexts[] = $expr->getExpression();
                    $orderByParams = [...$orderByParams, ...$expr->getParams()];
                }
                $orderBy = implode(', ', $orderByTexts);
            }
            $input = new Expr(
                "{$input->getExpression()} ORDER BY $orderBy",
                array_merge(
                    $input->getParams(),
                    $orderByParams,
                ),
            );
        }
        if ($filterWhere !== null) {
            $input = new Expr(
                "{$input->getExpression()}",
                array_merge(
                    $input->getParams(),
                    $filterWhere->getParams(),
                ),
            );
        }
        parent::__construct('jsonb_agg', [$input]);
    }

    public function getExpression($dialect = null): string
    {
        return parent::getExpression($dialect)
            . (
                $this->filterWhere !== null
                    ? " FILTER ( WHERE {$this->filterWhere->getExpression($dialect)} )"
                    : ''
            );
    }

    public function getParams(): array
    {
        return array_merge(
            parent::getParams(),
            $this->filterWhere?->getParams() ?: [],
        );
    }
}
