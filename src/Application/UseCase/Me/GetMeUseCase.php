<?php

namespace App\Application\UseCase\Me;

use App\Application\Aggregate\Me;
use App\Domain\Entity\User;
use App\Domain\Service\TelegramUser\TelegramServiceInterface;

class GetMeUseCase
{
    public function __construct(
        private TelegramServiceInterface $telegramService,
    ) {
    }

    public function execute(User $user): Me
    {
        return new Me($user, $this->telegramService->getConnectLink($user));
    }
}
