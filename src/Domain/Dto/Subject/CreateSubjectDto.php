<?php

namespace App\Domain\Dto\Subject;

readonly class CreateSubjectDto
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
