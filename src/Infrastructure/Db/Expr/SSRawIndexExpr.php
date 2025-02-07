<?php

namespace App\Infrastructure\Db\Expr;

use App\Domain\Dto\StudentSubject\GetSSByIndexRawDto;
use Qstart\Db\QueryBuilder\DML\Expression\ExprInterface;

class SSRawIndexExpr implements ExprInterface
{
    private int $time;

    public function __construct(
        private GetSSByIndexRawDto $dto,
        private string $ssAlias,
        private string $tsAlias,
        private string $suAlias,
        private string $tuAlias,
        private string $sAlias,
        private string $semAlias,
    ) {
        $this->ssAlias = trim($this->ssAlias, '.') . '.';
        $this->tsAlias = trim($this->tsAlias, '.') . '.';
        $this->suAlias = trim($this->suAlias, '.') . '.';
        $this->tuAlias = trim($this->tuAlias, '.') . '.';
        $this->sAlias = trim($this->sAlias, '.') . '.';
        $this->semAlias = trim($this->semAlias, '.') . '.';
        $this->time = rand();
    }

    /**
     * @inheritDoc
     */
    public function getExpression($dialect = null): string
    {
        return "lower({$this->suAlias}email) = lower(:sue_$this->time) "
            . "AND {$this->semAlias}year = :semyear_$this->time AND {$this->semAlias}spring = :sem_is_spring_$this->time "
            . "AND lower({$this->tuAlias}email) = lower(:tue_$this->time) "
            . "AND lower({$this->sAlias}name) = lower(:sname_$this->time) AND {$this->tsAlias}type = :tst_$this->time";
    }

    /**
     * @inheritDoc
     */
    public function getParams(): array
    {
        return [
            "sue_$this->time" => $this->dto->getStudentEmail()->getEmail(),
            "tue_$this->time" => $this->dto->getTeacherSubjectDto()->getTeacherEmail()->getEmail(),
            "tst_$this->time" => $this->dto->getTeacherSubjectDto()->getType()->value,
            "sname_$this->time" => $this->dto->getTeacherSubjectDto()->getSubjectName(),
            "semyear_$this->time" => $this->dto->getSemesterDto()->getYear(),
            "sem_is_spring_$this->time" => $this->dto->getSemesterDto()->isSpring(),
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
