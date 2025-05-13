<?php

namespace App\Application\UseCase\Me;

use App\Application\Aggregate\Me;
use App\Domain\Entity\User;
use App\Domain\Service\TelegramUser\TelegramLinkServiceInterface;

class GetMeUseCase
{
    public function __construct(
        private TelegramLinkServiceInterface $telegramLinkService,
    ) {
    }

    public function execute(User $user): Me
    {
        return new Me($user, $this->telegramLinkService->getConnectLink($user));
    }
}
