<?php

namespace App\Infrastructure\Db\Expr;

use Qstart\Db\QueryBuilder\DML\Expression\Expr;
use Qstart\Db\QueryBuilder\DML\Expression\ExprInterface;

class JsonbArrayElementsTextFunc extends AbstractFunc
{
    public function __construct(
        null|string|ExprInterface $input,
    ) {
        $oid = 'oid_' . spl_object_id($this);
        if (is_string($input)) {
            $input = new Expr(":{$oid}_input::jsonb", [":{$oid}_input" => $input]);
        } elseif ($input === null) {
            $input = new Expr('NULL::jsonb');
        }
        parent::__construct('jsonb_array_elements_text', [$input]);
    }
}
