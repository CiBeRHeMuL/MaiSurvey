<?php

namespace App\Infrastructure\Db\Expr;

use Qstart\Db\QueryBuilder\DML\Expression\ExprInterface;

abstract class AbstractFunc implements ExprInterface
{
    /**
     * @param string $name
     * @param (null|bool|int|float|string|ExprInterface)[] $params
     */
    public function __construct(
        protected string $name,
        protected array $params,
    ) {
    }

    public function getExpression($dialect = null): string
    {
        $oid = 'oid_' . spl_object_id($this);
        $params = implode(
            ', ',
            array_map(
                fn($e, $k) => match (true) {
                    $e instanceof ExprInterface => $e->getExpression($dialect),
                    $e === true => 'TRUE',
                    $e === false => 'FALSE',
                    $e === null => 'NULL',
                    is_string($e) => ":{$oid}_$k::text",
                    default => ":{$oid}_$k",
                },
                $this->params,
                array_keys($this->params),
            ),
        );
        return "$this->name($params)";
    }

    public function getParams(): array
    {
        $oid = 'oid_' . spl_object_id($this);
        return array_merge(...array_map(
            fn($e, $k) => match (true) {
                $e instanceof ExprInterface => $e->getParams(),
                $e === true,
                $e === false,
                $e === null => [],
                default => [":{$oid}_$k" => $e],
            },
            $this->params,
            array_keys($this->params),
        ));
    }

    final public function isEmpty(): bool
    {
        return false;
    }
}
