<?php

namespace App\Presentation\Messenger\Message;

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
        private bool $force = false,
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

    public function isForce(): bool
    {
        return $this->force;
    }

    public function getRefreshTime(): DateTimeImmutable
    {
        return $this->refreshTime;
    }
}
