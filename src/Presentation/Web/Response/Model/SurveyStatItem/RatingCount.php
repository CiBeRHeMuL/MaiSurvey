<?php

namespace App\Presentation\Web\Response\Model\SurveyStatItem;

readonly class RatingCount
{
    public function __construct(
        public int $count,
        public int $rating,
    ) {
    }
}
