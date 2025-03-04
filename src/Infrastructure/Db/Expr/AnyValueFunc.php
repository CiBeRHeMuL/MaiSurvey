<?php

namespace App\Infrastructure\Db\Expr;

use Qstart\Db\QueryBuilder\DML\Expression\ExprInterface;

class AnyValueFunc extends AbstractFunc
{
    public function __construct(
        bool|int|float|string|ExprInterface $input,
    ) {
        parent::__construct('any_value', [$input]);
    }
}
