<?php

namespace App\Infrastructure\Db\Expr;

use Qstart\Db\QueryBuilder\DML\Expression\ExprInterface;

class CoalesceFunc extends AbstractFunc
{
    public function __construct(
        null|bool|int|float|string|ExprInterface ...$input,
    ) {
        parent::__construct('coalesce', $input);
    }
}
