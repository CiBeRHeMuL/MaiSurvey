<?php

namespace App\Application\Aggregate;

use App\Domain\Entity\User;

readonly class Me
{
    public function __construct(
        private User $user,
        private string|null $telegramConnectLink,
    ) {
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getTelegramConnectLink(): ?string
    {
        return $this->telegramConnectLink;
    }
}
