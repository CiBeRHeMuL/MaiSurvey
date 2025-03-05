<?php

namespace App\Infrastructure\Messenger\Message;

use DateTimeImmutable;
use Symfony\Component\Messenger\Attribute\AsMessage;
use Symfony\Component\Uid\Uuid;

#[AsMessage('async')]
readonly class RefreshStatMessage
{
    private DateTimeImmutable $refreshTime;

    public function __construct(
        private Uuid $surveyId,
    ) {
        $this->refreshTime = new DateTimeImmutable();
    }

    public function getSurveyId(): Uuid
    {
        return $this->surveyId;
    }

    public function getRefreshTime(): DateTimeImmutable
    {
        return $this->refreshTime;
    }
}
