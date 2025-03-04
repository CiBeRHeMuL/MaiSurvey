<?php

namespace App\Infrastructure\Db\Expr;

use Qstart\Db\QueryBuilder\DML\Expression\ExprInterface;

class JsonbBuildObjectFunc extends AbstractFunc
{
    /**
     * @param (null|bool|int|float|string|ExprInterface)[] $params
     */
    public function __construct(
        array $params,
    ) {
        parent::__construct('jsonb_build_object', $params);
    }
}
