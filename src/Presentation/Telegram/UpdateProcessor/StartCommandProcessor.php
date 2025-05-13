<?php

namespace App\Presentation\Telegram\UpdateProcessor;

use AndrewGos\TelegramBot\Request\SendMessageRequest;
use AndrewGos\TelegramBot\UpdateHandler\UpdateProcessor\AbstractCommandMessageUpdateProcessor;
use AndrewGos\TelegramBot\ValueObject\ChatId;

class StartCommandProcessor extends AbstractCommandMessageUpdateProcessor
{
    use AuthUpdateProcessorTrait;

    public function beforeProcess(): bool
    {
        $chatId = new ChatId($this->message->getChat()->getId());
        if ($this->authenticate($chatId)) {
            $this->getApi()->sendMessage(
                new SendMessageRequest(
                    $chatId,
                    'Ты уже привязал свой Telegram к профилю!',
                ),
            );
            return false;
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function process(): void
    {
        $this->getApi()->sendMessage(
            new SendMessageRequest(
                new ChatId($this->message->getChat()->getId()),
                'Ура!',
            ),
        );
    }
}
