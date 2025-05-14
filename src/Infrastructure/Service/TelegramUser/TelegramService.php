<?php

namespace App\Infrastructure\Service\TelegramUser;

use AndrewGos\TelegramBot\Request\GetChatRequest;
use AndrewGos\TelegramBot\Telegram;
use AndrewGos\TelegramBot\ValueObject\ChatId as AGChatId;
use App\Domain\Dto\TelegramUser\ChatId;
use App\Domain\Entity\User;
use App\Domain\Service\TelegramUser\TelegramServiceInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class TelegramService implements TelegramServiceInterface
{
    public function __construct(
        private Telegram $telegram,
        private CacheInterface $cache,
    ) {
    }

    public function getConnectLink(User $user): string|null
    {
        if ($user->canConnectTelegram()) {
            return "https://t.me/{$this->getTelegramMe()->getUsername()}?start={$user->getTelegramConnectId()->toRfc4122()}";
        }
        return null;
    }

    public function checkChat(ChatId $chatId): bool
    {
        return $this->cache->get(
            $this->computeCacheKey("chat-{$chatId->getId()}"),
            function (ItemInterface $item) use ($chatId) {
                $item->expiresAfter(3600 * 72);

                $response = $this->telegram->getApi()
                    ->getChat(
                        new GetChatRequest(
                            new AGChatId(ctype_digit($chatId->getId()) ? (int)$chatId->getId() : $chatId->getId()),
                        ),
                    );
                return $response->getChatFullInfo() !== null;
            },
        );
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
