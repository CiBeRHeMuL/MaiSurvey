<?php

namespace App\Presentation\Web\Response\Model;

use App\Domain\Entity\Subject as DomainSubject;

readonly class Subject
{
    public function __construct(
        public string $id,
        public string $name,
        public Semester $semester,
    ) {
    }

    public static function fromSubject(DomainSubject $subject): self
    {
        return new self(
            $subject->getId()->toRfc4122(),
            $subject->getName(),
            Semester::fromSemester($subject->getSemester()),
        );
    }
}
