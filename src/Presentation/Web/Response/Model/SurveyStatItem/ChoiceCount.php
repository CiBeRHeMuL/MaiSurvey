<?php

namespace App\Presentation\Web\Response\Model\SurveyStatItem;

readonly class ChoiceCount
{
    public function __construct(
        public int $count,
        public string $choice,
    ) {
    }
}
