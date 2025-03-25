<?php

namespace App\Domain\Dto\SurveyStat;

readonly class StatNCUser
{
    public function __construct(
        public string $group,
        public string $name,
    ) {
    }

    public function getGroup(): string
    {
        return $this->group;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
