<?php

namespace App\Infrastructure\Messenger\Message;

use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage(['async', 'sync'])]
readonly class RefreshViewsMessage
{
    public function __construct(
        private array $views,
    ) {
    }

    public function getViews(): array
    {
        return $this->views;
    }
}
