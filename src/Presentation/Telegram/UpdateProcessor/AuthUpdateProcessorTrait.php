<?php

namespace App\Presentation\Telegram\UpdateProcessor;

use AndrewGos\TelegramBot\ValueObject\ChatId;
use App\Application\UseCase\TelegramUser\GetByChatIdUseCase;
use App\Domain\Entity\TelegramUser;
use Throwable;

trait AuthUpdateProcessorTrait
{
    private TelegramUser|null $telegramUser = null;

    public function __construct(
        private GetByChatIdUseCase $getByChatIdUseCase,
    ) {
    }

    public function setGetByChatIdUseCase(GetByChatIdUseCase $getByChatIdUseCase): static
    {
        $this->getByChatIdUseCase = $getByChatIdUseCase;
        return $this;
    }

    public function authenticate(ChatId $chatId): bool
    {
        try {
            $this->telegramUser = $this->getByChatIdUseCase->execute((string)$chatId->getId());
            return $this->telegramUser !== null;
        } catch (Throwable $e) {
            return false;
        }
    }

    public function getTelegramUser(): ?TelegramUser
    {
        return $this->telegramUser;
    }
}
