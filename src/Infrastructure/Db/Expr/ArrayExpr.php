<?php

namespace App\Infrastructure\Db\Expr;

use Qstart\Db\QueryBuilder\DML\Expression\ExprInterface;

class ArrayExpr implements ExprInterface
{
    /**
     * @param (null|bool|int|float|string|ExprInterface)[] $array
     * @param string $type
     */
    public function __construct(
        private array $array,
        private string $type,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getExpression($dialect = null): string
    {
        $oid = 'oid_' . spl_object_id($this);
        $array = implode(
            ', ',
            array_map(
                function ($el, int|string $k) use ($dialect, $oid) {
                    return $el instanceof ExprInterface
                        ? $el->getExpression($dialect)
                        : ":{$oid}_{$k}_el";
                },
                $this->array,
                array_keys($this->array),
            )
        );
        return "ARRAY[$array]::{$this->type}[]";
    }

    /**
     * @inheritDoc
     */
    public function getParams(): array
    {
        $oid = 'oid_' . spl_object_id($this);
        return array_merge(array_map(
            function ($el, int|string $k) use ($oid) {
                return $el instanceof ExprInterface
                    ? $el->getParams()
                    : [":{$oid}_{$k}_el" => $el];
            },
            $this->array,
            array_keys($this->array),
        ));
    }

    /**
     * @inheritDoc
     */
    public function isEmpty(): bool
    {
        return false;
    }
}
