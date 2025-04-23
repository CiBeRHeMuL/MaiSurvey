<?php

namespace App\Infrastructure\Db\Expr;

use App\Domain\Helper\HString;
use Qstart\Db\QueryBuilder\DML\Expression\ExprInterface;

class ILikeExpr implements ExprInterface
{
    private array $params = [];

    public function __construct(
        private ExprInterface $left,
        private string|ExprInterface $right,
    ) {
        if (!$this->right instanceof ExprInterface) {
            $rus = HString::changeEngKeyboardLayoutToRus($this->right);
            $eng = HString::changeRusKeyboardLayoutToEng($this->right);

            $escapedChars = '.^$*+?()[{}]|';
            $rus = addcslashes($rus, $escapedChars);
            $eng = addcslashes($eng, $escapedChars);

            $right = [
                $this->right,
                $eng,
                $rus,
                preg_replace('/[еёЕЁ]/u', '[еёЕЁ]', $rus),
            ];

            $oid = spl_object_id($this);
            $this->params = array_combine(
                array_map(fn($i) => ":ile_{$oid}_$i", range(0, count($right) - 1)),
                $right,
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function getExpression($dialect = null): string
    {
        if ($this->right instanceof ExprInterface) {
            return "({$this->left->getExpression($dialect)}) ILIKE ({$this->right->getExpression($dialect)})";
        } else {
            return implode(
                ' OR ',
                array_map(
                    fn($e) => "({$this->left->getExpression($dialect)}) ~* $e",
                    array_keys($this->params),
                ),
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function getParams(): array
    {
        if ($this->right instanceof ExprInterface) {
            return array_merge(
                $this->left->getParams(),
                $this->right->getParams(),
            );
        }
        return array_merge(
            $this->left->getParams(),
            $this->params,
        );
    }

    /**
     * @inheritDoc
     */
    public function isEmpty(): bool
    {
        return false;
    }
}
