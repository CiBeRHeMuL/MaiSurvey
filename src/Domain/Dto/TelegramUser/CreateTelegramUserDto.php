<?php

namespace App\Domain\Dto\TelegramUser;

use App\Domain\Entity\User;

readonly class CreateTelegramUserDto
{
    public function __construct(
        private User $user,
        private ChatId $chatId,
    ) {
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getChatId(): ChatId
    {
        return $this->chatId;
    }
}
