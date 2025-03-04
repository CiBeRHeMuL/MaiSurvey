<?php

namespace App\Infrastructure\Db\Expr;

use Qstart\Db\QueryBuilder\DML\Expression\Expr;
use Qstart\Db\QueryBuilder\DML\Expression\ExprInterface;

class JsonbPathQueryFunc extends AbstractFunc
{
    public function __construct(
        string|ExprInterface $jsonb,
        string $path,
        array|null $vars = null,
        bool|null $silent = null,
    ) {
        $params = [
            $jsonb,
            new Expr("'$path'"),
        ];
        if ($vars !== null) {
            $params[] = json_encode($vars);
            if ($silent !== null) {
                $params[] = $silent;
            }
        }
        parent::__construct(
            'jsonb_path_query',
            $params,
        );
    }
}
