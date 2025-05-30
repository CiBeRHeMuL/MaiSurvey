<?php

namespace App\Infrastructure\Db\Expr;

use Qstart\Db\QueryBuilder\DML\Expression\ExprInterface;

class FullNameExpr implements ExprInterface
{
    public function __construct(
        private string|null $alias = null,
    ) {
        $this->alias = $this->alias !== null
            ? trim($this->alias, '.') . '.'
            : null;
    }

    /**
     * @inheritDoc
     */
    public function getExpression($dialect = null): string
    {
        return "concat({$this->alias}last_name, ' ', {$this->alias}first_name, CASE {$this->alias}patronymic IS NULL WHEN TRUE THEN '' ELSE ' ' || {$this->alias}patronymic END)";
    }

    /**
     * @inheritDoc
     */
    public function getParams(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function isEmpty(): bool
    {
        return false;
    }
}
