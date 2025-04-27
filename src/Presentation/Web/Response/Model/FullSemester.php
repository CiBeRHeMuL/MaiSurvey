<?php

namespace App\Presentation\Web\Response\Model;

use App\Domain\Entity\Semester as DomainSemester;

readonly class FullSemester
{
    public function __construct(
        public string $id,
        public string $name,
    ) {
    }

    public static function fromSemester(DomainSemester $semester): self
    {
        return new self(
            $semester->getId()->toRfc4122(),
            $semester->getName(),
        );
    }
}
