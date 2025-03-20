<?php

namespace App\Infrastructure\Db\Expr;

use App\Domain\Enum\SurveyStatusEnum;
use Qstart\Db\QueryBuilder\DML\Expression\ExprInterface;

class ActualSurveyExpr implements ExprInterface
{
    private string $oid;

    public function __construct(
        private string|null $alias = null,
        private bool $not = false,
    ) {
        $this->alias = $this->alias !== null ? trim($this->alias, '.') . '.' : null;
        $this->oid = spl_object_id($this);
    }

    /**
     * @inheritDoc
     */
    public function getExpression($dialect = null): string
    {
        $expr = "{$this->alias}actual_to >= now() AND {$this->alias}status = :{$this->oid}_status";
        return $this->not ? "NOT ($expr)" : $expr;
    }

    /**
     * @inheritDoc
     */
    public function getParams(): array
    {
        return [
            "{$this->oid}_status" => SurveyStatusEnum::Active->value,
        ];
    }

    /**
     * @inheritDoc
     */
    public function isEmpty(): bool
    {
        return false;
    }
}
