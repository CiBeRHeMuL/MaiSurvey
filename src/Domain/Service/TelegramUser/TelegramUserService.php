<?php

namespace App\Domain\Service\TelegramUser;

use App\Domain\Dto\TelegramUser\ChatId;
use App\Domain\Entity\TelegramUser;
use App\Domain\Repository\TelegramUserRepositoryInterface;
use Psr\Log\LoggerInterface;

class TelegramUserService
{
    public function __construct(
        private LoggerInterface $logger,
        private TelegramUserRepositoryInterface $telegramUserRepository,
    ) {
    }

    public function setLogger(LoggerInterface $logger): TelegramUserService
    {
        $this->logger = $logger;
        return $this;
    }

    public function getByChatId(ChatId $chatId): TelegramUser|null
    {
        return $this->telegramUserRepository->findByChatId($chatId);
    }
}
