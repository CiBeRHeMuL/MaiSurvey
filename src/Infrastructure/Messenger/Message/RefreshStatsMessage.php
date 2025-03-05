<?php

namespace App\Infrastructure\Messenger\Message;

use DateTimeImmutable;
use Symfony\Component\Messenger\Attribute\AsMessage;
use Symfony\Component\Uid\Uuid;

#[AsMessage('async')]
readonly class RefreshStatsMessage
{
    private DateTimeImmutable $refreshTime;

    /**
     * @param Uuid[]|null $surveyIds
     */
    public function __construct(
        private array|null $surveyIds,
    ) {
        $this->refreshTime = new DateTimeImmutable();
    }

    /**
     * @return Uuid[]|null
     */
    public function getSurveyIds(): array|null
    {
        return $this->surveyIds;
    }

    public function getRefreshTime(): DateTimeImmutable
    {
        return $this->refreshTime;
    }
}
