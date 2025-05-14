<?php

namespace App\Domain\Service\TelegramUser;

use App\Domain\Dto\TelegramUser\ChatId;
use App\Domain\Entity\User;

interface TelegramServiceInterface
{
    public function getConnectLink(User $user): string|null;

    public function checkChat(ChatId $chatId): bool;
}
