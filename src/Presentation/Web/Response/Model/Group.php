<?php

namespace App\Presentation\Web\Response\Model;

use App\Domain\Entity\Group as DomainGroup;

readonly class Group
{
    public function __construct(
        public string $id,
        public string $name,
    ) {
    }

    public static function fromGroup(DomainGroup $group): self
    {
        return new self(
            $group->getId()->toRfc4122(),
            $group->getName(),
        );
    }
}
