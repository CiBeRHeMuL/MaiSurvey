<?php

namespace App\Presentation\Telegram\Messenger\Handler;

use AndrewGos\TelegramBot\Entity\Update;
use AndrewGos\TelegramBot\Telegram;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class TelegramRequestHandler
{
    public function __construct(
        private Telegram $telegram,
    ) {
    }

    public function __invoke(Update $update): void
    {
        $this->telegram->getUpdateHandler()->processUpdate($update);
    }
}
