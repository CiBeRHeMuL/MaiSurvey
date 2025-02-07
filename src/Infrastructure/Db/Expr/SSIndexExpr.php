<?php

namespace App\Infrastructure\Db\Expr;

use App\Domain\Dto\StudentSubject\GetSSByIndexDto;
use Qstart\Db\QueryBuilder\DML\Expression\ExprInterface;

class SSIndexExpr implements ExprInterface
{
    public function __construct(
        private GetSSByIndexDto $dto,
        private string|null $alias = null,
    ) {
        $this->alias = $this->alias !== null
            ? trim($this->alias, '.') . '.'
            : null;
    }

    /**
     * @inheritDoc
     */
    public function getExpression($dialect = null): string
    {
        return "{$this->alias}user_id = :uid "
            . "AND {$this->alias}teacher_subject_id = :tsid";
    }

    /**
     * @inheritDoc
     */
    public function getParams(): array
    {
        return [
            'uid' => $this->dto->getStudentId()->toRfc4122(),
            'tsid' => $this->dto->getTeacherSubjectId()->toRfc4122(),
        ];
    }

    /**
     * @inheritDoc
     */
    public function isEmpty(): bool
    {
        return false;
    }
}
