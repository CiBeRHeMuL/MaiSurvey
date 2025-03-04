<?php

namespace App\Infrastructure\Db\Expr;

use Qstart\Db\QueryBuilder\DML\Expression\ExprInterface;

class CastExpr implements ExprInterface
{
    public function __construct(
        private null|bool|int|float|string|ExprInterface $expr,
        private string $type,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getExpression($dialect = null): string
    {
        $oid = 'oid_' . spl_object_id($this);
        $expr = $this->expr instanceof ExprInterface
            ? "({$this->expr->getExpression($dialect)})"
            : ":{$oid}_expr";
        return "cast($expr AS $this->type)";
    }

    /**
     * @inheritDoc
     */
    public function getParams(): array
    {
        $oid = 'oid_' . spl_object_id($this);
        return $this->expr instanceof ExprInterface
            ? $this->expr->getParams()
            : [":{$oid}_expr" => $this->expr];
    }

    /**
     * @inheritDoc
     */
    public function isEmpty(): bool
    {
        return false;
    }
}
