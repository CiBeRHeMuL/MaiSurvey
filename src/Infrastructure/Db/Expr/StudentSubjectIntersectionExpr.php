<?php

namespace App\Infrastructure\Db\Expr;

use App\Domain\Dto\StudentSubject\GetStudentSubjectByIntersectionDto;
use Qstart\Db\QueryBuilder\DML\Expression\ExprInterface;

class StudentSubjectIntersectionExpr implements ExprInterface
{
    public function __construct(
        private GetStudentSubjectByIntersectionDto $dto,
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
            . "AND {$this->alias}teacher_subject_id = :tsid "
            . "AND tsrange({$this->alias}actual_from, {$this->alias}actual_to, '[]') && tsrange(:acfrom, :acto)";
    }

    /**
     * @inheritDoc
     */
    public function getParams(): array
    {
        return [
            'uid' => $this->dto->getStudentId()->toRfc4122(),
            'tsid' => $this->dto->getTeacherSubjectId()->toRfc4122(),
            'acfrom' => $this->dto->getActualFrom()->format(DATE_RFC3339),
            'acto' => $this->dto->getActualTo()->format(DATE_RFC3339),
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
