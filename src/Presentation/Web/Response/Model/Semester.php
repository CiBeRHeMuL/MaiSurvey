<?php

namespace App\Presentation\Web\Response\Model;

use App\Domain\Entity\Semester as DomainSemester;

readonly class Semester
{
    public function __construct(
        public string $name,
    ) {
    }

    public static function fromSemester(DomainSemester $semester): self
    {
        return new self(
            $semester->getName(),
        );
    }
}
