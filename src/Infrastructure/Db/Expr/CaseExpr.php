<?php

namespace App\Infrastructure\Db\Expr;

use Qstart\Db\QueryBuilder\DML\Expression\ExprInterface;

class CaseExpr implements ExprInterface
{
    /**
     * @param (array{0: bool|ExprInterface, 1: null|bool|int|float|string|ExprInterface})[] $cases
     * @param bool|int|float|string|ExprInterface|null $expr
     * @param bool|int|float|string|ExprInterface|null $else
     */
    public function __construct(
        private array $cases,
        private null|bool|int|float|string|ExprInterface $expr = true,
        private null|bool|int|float|string|ExprInterface $else = null,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getExpression($dialect = null): string
    {
        $oid = 'oid_' . spl_object_id($this);
        $expr = match (true) {
            $this->expr instanceof ExprInterface => $this->expr->getExpression($dialect),
            $this->expr === true => 'TRUE',
            $this->expr === false => 'FALSE',
            $this->expr === null => 'NULL',
            default => ":{$oid}_expr",
        };
        $else = match (true) {
            $this->else instanceof ExprInterface => $this->else->getExpression($dialect),
            $this->else === true => 'TRUE',
            $this->else === false => 'FALSE',
            $this->else === null => 'NULL',
            default => ":{$oid}_else",
        };

        if ($this->cases === []) {
            return "$else";
        }
        $cases = implode(
            ' ',
            array_map(
                function (array $el, int|string $k) use ($dialect, $oid) {
                    $when = $el[0] instanceof ExprInterface
                        ? "({$el[0]->getExpression($dialect)})"
                        : ":{$oid}_{$k}_when";
                    $then = $el[1] instanceof ExprInterface
                        ? "({$el[1]->getExpression($dialect)})"
                        : ":{$oid}_{$k}_then";
                    return "WHEN $when THEN $then";
                },
                $this->cases,
                array_keys($this->cases),
            ),
        );
        return "CASE $expr $cases ELSE $else END";
    }

    /**
     * @inheritDoc
     */
    public function getParams(): array
    {
        $oid = 'oid_' . spl_object_id($this);
        return array_merge(
            match (true) {
                $this->expr instanceof ExprInterface => $this->expr->getParams(),
                $this->expr === true,
                    $this->expr === false,
                    $this->expr === null => [],
                default => [":{$oid}_expr" => $this->expr],
            },
            match (true) {
                $this->else instanceof ExprInterface => $this->else->getParams(),
                $this->else === true,
                    $this->else === false,
                    $this->else === null => [],
                default => [":{$oid}_else" => $this->else],
            },
            ...array_map(
            function (array $el, int|string $k) use ($oid) {
                return array_merge(
                    $el[0] instanceof ExprInterface
                        ? $el[0]->getParams()
                        : [":{$oid}_{$k}_when" => $el[0]],
                    $el[1] instanceof ExprInterface
                        ? $el[1]->getParams()
                        : [":{$oid}_{$k}_then" => $el[1]],
                );
            },
            $this->cases,
            array_keys($this->cases),
        ),
        );
    }

    /**
     * @inheritDoc
     */
    public function isEmpty(): bool
    {
        return false;
    }
}
