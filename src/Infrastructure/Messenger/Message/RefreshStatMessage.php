<?php

namespace App\Infrastructure\Messenger\Message;

use Symfony\Component\Messenger\Attribute\AsMessage;
use Symfony\Component\Uid\Uuid;

#[AsMessage('async')]
readonly class RefreshStatMessage
{
    public function __construct(
        private Uuid $surveyId,
    ) {
    }

    public function getSurveyId(): Uuid
    {
        return $this->surveyId;
    }
}
