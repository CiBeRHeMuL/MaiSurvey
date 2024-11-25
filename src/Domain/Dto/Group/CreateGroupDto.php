<?php

namespace App\Domain\Dto\Group;

readonly class CreateGroupDto
{
    public function __construct(
        private string $name,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }
}
