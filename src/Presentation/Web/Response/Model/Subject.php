<?php

namespace App\Presentation\Web\Response\Model;

use App\Domain\Entity\Subject as DomainSubject;

readonly class Subject
{
    public function __construct(
        public string $id,
        public string $name,
    ) {
    }

    public static function fromSubject(DomainSubject $group): self
    {
        return new self(
            $group->getId()->toRfc4122(),
            $group->getName(),
        );
    }
}
