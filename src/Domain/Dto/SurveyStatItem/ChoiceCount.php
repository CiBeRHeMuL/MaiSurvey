<?php

namespace App\Domain\Dto\SurveyStatItem;

readonly class ChoiceCount
{
    public function __construct(
        public int $count,
        public string $choice,
    ) {
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function getChoice(): string
    {
        return $this->choice;
    }
}
