<?php

namespace App\Infrastructure\Db\Expr;

use Qstart\Db\QueryBuilder\DML\Expression\ExprInterface;

class ArrayPositionFunc extends AbstractFunc
{
    public function __construct(
        array|ExprInterface $expr,
        null|bool|int|float|string|ExprInterface $el,
        int|null $start = null,
    ) {
        if (is_array($expr)) {
            $expr = new ArrayExpr($expr, 'text');
        }
        $params = [
            $expr,
            $el,
        ];
        if ($start !== null) {
            $params[] = $start;
        }
        parent::__construct(
            'array_position',
            $params,
        );
    }
}
