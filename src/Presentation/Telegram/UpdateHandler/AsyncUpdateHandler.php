<?php

namespace App\Presentation\Telegram\UpdateHandler;

use AndrewGos\TelegramBot\Api\ApiInterface;
use AndrewGos\TelegramBot\Entity\Update;
use AndrewGos\TelegramBot\UpdateHandler\UpdateHandler;
use AndrewGos\TelegramBot\UpdateHandler\UpdateSource\UpdateSourceInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class AsyncUpdateHandler extends UpdateHandler
{
    public function __construct(
        private MessageBusInterface $messageBus,
        UpdateSourceInterface $updateSource,
        ApiInterface $api,
        LoggerInterface $logger,
    ) {
        parent::__construct($updateSource, $api, $logger);
    }

    public function processUpdate(Update $update): void
    {
        $this->messageBus->dispatch($update);
    }
}
