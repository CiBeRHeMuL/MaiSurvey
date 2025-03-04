<?php

namespace App\Infrastructure\Db\Expr;

use Qstart\Db\QueryBuilder\DML\Expression\ExprInterface;

class SumFunc extends AbstractFunc
{
    public function __construct(
        bool|int|float|ExprInterface $input,
    ) {
        parent::__construct('sum', [$input]);
    }
}
