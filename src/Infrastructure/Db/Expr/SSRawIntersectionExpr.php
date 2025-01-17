<?php

namespace App\Infrastructure\Db\Expr;

use App\Domain\Dto\StudentSubject\GetSSByIntersectionRawDto;
use Qstart\Db\QueryBuilder\DML\Expression\ExprInterface;

class SSRawIntersectionExpr implements ExprInterface
{
    private int $time;

    public function __construct(
        private GetSSByIntersectionRawDto $dto,
        private string $ssAlias,
        private string $tsAlias,
        private string $suAlias,
        private string $tuAlias,
        private string $sAlias,
    ) {
        $this->ssAlias = trim($this->ssAlias, '.') . '.';
        $this->tsAlias = trim($this->tsAlias, '.') . '.';
        $this->suAlias = trim($this->suAlias, '.') . '.';
        $this->tuAlias = trim($this->tuAlias, '.') . '.';
        $this->sAlias = trim($this->sAlias, '.') . '.';
        $this->time = rand();
    }

    /**
     * @inheritDoc
     */
    public function getExpression($dialect = null): string
    {
        return "lower({$this->suAlias}email) = lower(:sue_$this->time) "
            . "AND tsrange({$this->ssAlias}actual_from, {$this->ssAlias}actual_to, '[]') && tsrange(:acfrom_$this->time, :acto_$this->time) "
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
            "acfrom_$this->time" => $this->dto->getActualFrom()->format(DATE_RFC3339),
            "acto_$this->time" => $this->dto->getActualTo()->format(DATE_RFC3339),
            "sname_$this->time" => $this->dto->getTeacherSubjectDto()->getSubjectName(),
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
