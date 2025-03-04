<?php

namespace App\Infrastructure\Db\Expr;

use Qstart\Db\QueryBuilder\DML\Expression\ExprInterface;

class ArrayFromJsonbFunc extends AbstractFunc
{
    public function __construct(
        string|ExprInterface $input,
    ) {
        parent::__construct('array_from_jsonb', [$input]);
    }
}
