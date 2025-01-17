<?php

namespace App\Infrastructure\Db\Expr;

use App\Domain\Dto\StudentSubject\GetSSByIntersectionRawDto;
use Qstart\Db\QueryBuilder\DML\Expression\ExprInterface;

class SSRawIntersectionExpr implements ExprInterface
{
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
    }

    /**
     * @inheritDoc
     */
    public function getExpression($dialect = null): string
    {
        return "lower({$this->suAlias}email) = lower(:sue) "
            . "AND tsrange({$this->ssAlias}actual_from, {$this->ssAlias}actual_to, '[]') && tsrange(:acfrom, :acto) "
            . "AND lower({$this->tuAlias}email) = lower(:tue) AND lower({$this->sAlias}name) = lower(:sname) AND {$this->tsAlias}type = :tst";
    }

    /**
     * @inheritDoc
     */
    public function getParams(): array
    {
        return [
            'sue' => $this->dto->getStudentEmail()->getEmail(),
            'tue' => $this->dto->getTeacherSubjectDto()->getTeacherEmail()->getEmail(),
            'tst' => $this->dto->getTeacherSubjectDto()->getType()->value,
            'acfrom' => $this->dto->getActualFrom()->format(DATE_RFC3339),
            'acto' => $this->dto->getActualTo()->format(DATE_RFC3339),
            'sname' => $this->dto->getTeacherSubjectDto()->getSubjectName(),
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
