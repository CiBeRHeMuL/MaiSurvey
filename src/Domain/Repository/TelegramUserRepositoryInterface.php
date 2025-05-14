<?php

namespace App\Domain\Repository;

use App\Domain\Dto\TelegramUser\ChatId;
use App\Domain\Entity\TelegramUser;
use Symfony\Component\Uid\Uuid;

interface TelegramUserRepositoryInterface extends Common\RepositoryInterface
{
    public function findByChatId(ChatId $chatId): TelegramUser|null;

    public function findByUserId(Uuid $id): TelegramUser|null;
}
