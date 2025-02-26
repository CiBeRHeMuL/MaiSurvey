<?php

namespace App\Domain\Dto\SurveyStatItem;

readonly class RatingCount
{
    public function __construct(
        public int $count,
        public int $rating,
    ) {
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function getRating(): int
    {
        return $this->rating;
    }
}
