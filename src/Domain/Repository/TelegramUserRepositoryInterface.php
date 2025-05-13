<?php

namespace App\Domain\Repository;

use App\Domain\Dto\TelegramUser\ChatId;
use App\Domain\Entity\TelegramUser;

interface TelegramUserRepositoryInterface extends Common\RepositoryInterface
{
    public function findByChatId(ChatId $chatId): TelegramUser|null;
}
