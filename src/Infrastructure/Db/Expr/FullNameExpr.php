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
        return "concat({$this->alias}first_name, ' ', {$this->alias}last_name, ' ', {$this->alias}patronymic)";
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
