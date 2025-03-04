<?php

namespace App\Infrastructure\Db\Expr;

use Qstart\Db\QueryBuilder\DML\Expression\Expr;
use Qstart\Db\QueryBuilder\DML\Expression\ExprInterface;

class JsonbAggFunc extends AbstractFunc
{
    public function __construct(
        null|bool|int|float|string|ExprInterface $input,
        ExprInterface|null $orderBy = null,
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
            $input = new Expr(
                "{$input->getExpression()} ORDER BY {$orderBy->getExpression()}",
                array_merge(
                    $input->getParams(),
                    $orderBy->getParams(),
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
