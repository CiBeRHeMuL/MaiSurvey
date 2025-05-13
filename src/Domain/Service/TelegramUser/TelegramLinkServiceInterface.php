<?php

namespace App\Domain\Service\TelegramUser;

use App\Domain\Entity\User;

interface TelegramLinkServiceInterface
{
    public function getConnectLink(User $user): string|null;
}
