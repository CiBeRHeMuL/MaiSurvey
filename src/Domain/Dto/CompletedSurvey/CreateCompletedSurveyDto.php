<?php

namespace App\Domain\Dto\CompletedSurvey;

use App\Domain\Entity\Survey;
use App\Domain\Entity\User;

readonly class CreateCompletedSurveyDto
{
    public function __construct(
        private Survey $survey,
        private User $user,
    ) {
    }

    public function getSurvey(): Survey
    {
        return $this->survey;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
