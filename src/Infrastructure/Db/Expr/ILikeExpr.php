<?php

namespace App\Infrastructure\Db\Expr;

use Qstart\Db\QueryBuilder\DML\Expression\ExprInterface;

class ILikeExpr implements ExprInterface
{
    public function __construct(
        private string|ExprInterface|null $left,
        private string|ExprInterface|null $right,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getExpression($dialect = null): string
    {
        $leftExpr = $this->left instanceof ExprInterface ? $this->left->getExpression($dialect) : "'$this->left'";
        $rightExpr = $this->right instanceof ExprInterface ? $this->right->getExpression($dialect) : "'$this->right'";
        return "($leftExpr) ILIKE concat('%', ($rightExpr), '%')";
    }

    /**
     * @inheritDoc
     */
    public function getParams(): array
    {
        return array_merge(
            $this->left instanceof ExprInterface ? $this->left->getParams() : [],
            $this->right instanceof ExprInterface ? $this->right->getParams() : [],
        );
    }

    /**
     * @inheritDoc
     */
    public function isEmpty(): bool
    {
        $leftIsEmpty = !$this->left || ($this->left instanceof ExprInterface && $this->left->isEmpty());
        $rightIsEmpty = !$this->right || ($this->right instanceof ExprInterface && $this->right->isEmpty());
        return $leftIsEmpty || $rightIsEmpty;
    }
}
