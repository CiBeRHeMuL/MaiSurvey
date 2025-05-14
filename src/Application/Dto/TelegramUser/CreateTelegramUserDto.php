<?php

namespace App\Application\Dto\TelegramUser;

use App\Application\Validator\Constraints as LAssert;
use Symfony\Component\Validator\Constraints as Assert;

readonly class CreateTelegramUserDto
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Uuid]
        public string $telegram_connect_id,
        #[Assert\NotBlank]
        #[LAssert\TelegramChatId]
        public string $chat_id,
    ) {
    }
}
