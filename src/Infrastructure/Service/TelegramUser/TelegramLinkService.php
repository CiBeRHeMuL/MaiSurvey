<?php

namespace App\Infrastructure\Service\TelegramUser;

use AndrewGos\TelegramBot\Telegram;
use App\Domain\Entity\User;
use App\Domain\Service\TelegramUser\TelegramLinkServiceInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class TelegramLinkService implements TelegramLinkServiceInterface
{
    public function __construct(
        private Telegram $telegram,
        private CacheInterface $cache,
    ) {
    }

    public function getConnectLink(User $user): string|null
    {
        if ($user->canConnectTelegram()) {
            return "https://t.me/{$this->getTelegramMe()->getFirstName()}?start={$user->getTelegramConnectId()->toRfc4122()}";
        }
        return null;
    }

    private function getTelegramMe(): \AndrewGos\TelegramBot\Entity\User
    {
        return $this->cache->get(
            $this->computeCacheKey('me'),
            function (ItemInterface $item) {
                $item->expiresAfter(null);

                return $this->telegram->getMe();
            },
        );
    }

    private function computeCacheKey(string $type): string
    {
        $token = $this->telegram->getToken()->getToken();
        $reserved = ItemInterface::RESERVED_CHARACTERS;
        $token = str_replace(str_split($reserved), "\t", $token);

        return "telegram-$type-$token";
    }
}
