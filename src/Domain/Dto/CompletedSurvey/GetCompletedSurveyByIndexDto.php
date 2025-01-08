<?php

namespace App\Domain\Dto\CompletedSurvey;

use Symfony\Component\Uid\Uuid;

readonly class GetCompletedSurveyByIndexDto
{
    public function __construct(
        private Uuid $surveyId,
        private Uuid $userId,
    ) {
    }

    public function getSurveyId(): Uuid
    {
        return $this->surveyId;
    }

    public function getUserId(): Uuid
    {
        return $this->userId;
    }
}
