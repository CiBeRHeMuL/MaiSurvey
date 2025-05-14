<?php

namespace App\Domain\Service\TelegramUser;

use App\Domain\Dto\TelegramUser\ChatId;
use App\Domain\Dto\TelegramUser\CreateTelegramUserDto;
use App\Domain\Entity\TelegramUser;
use App\Domain\Entity\User;
use App\Domain\Enum\ConnectTelegramResultEnum;
use App\Domain\Enum\ValidationErrorSlugEnum;
use App\Domain\Exception\ErrorException;
use App\Domain\Exception\ValidationException;
use App\Domain\Repository\TelegramUserRepositoryInterface;
use App\Domain\Validation\ValidationError;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;

class TelegramUserService
{
    public function __construct(
        private LoggerInterface $logger,
        private TelegramUserRepositoryInterface $telegramUserRepository,
        private TelegramServiceInterface $telegramService,
    ) {
    }

    public function setLogger(LoggerInterface $logger): TelegramUserService
    {
        $this->logger = $logger;
        return $this;
    }

    public function getByChatId(ChatId $chatId): TelegramUser|null
    {
        return $this->telegramUserRepository->findByChatId($chatId);
    }

    public function getForUser(User $user): TelegramUser|null
    {
        return $this->telegramUserRepository->findByUserId($user->getId());
    }

    public function create(CreateTelegramUserDto $dto): ConnectTelegramResultEnum
    {
        $chatExists = $this->telegramService->checkChat($dto->getChatId());
        if ($chatExists === false) {
            throw ValidationException::new([
                new ValidationError(
                    'chat_id',
                    ValidationErrorSlugEnum::NotFound->getSlug(),
                    'Чат не найден',
                ),
            ]);
        }

        $existing = $this->telegramUserRepository->findByUserId($dto->getUser()->getId());
        if ($existing !== null) {
            return ConnectTelegramResultEnum::AlreadyConnected;
        }

        $existing = $this->telegramUserRepository->findByChatId($dto->getChatId());
        if ($existing !== null) {
            return ConnectTelegramResultEnum::ConnectedToAnother;
        }

        $telegramUser = new TelegramUser();
        $telegramUser->setUserId($dto->getUser()->getId())
            ->setChatId($dto->getChatId())
            ->setUser($dto->getUser())
            ->setCreatedAt(new DateTimeImmutable())
            ->setUpdatedAt(new DateTimeImmutable());

        if ($this->telegramUserRepository->create($telegramUser) === false) {
            throw ErrorException::new('Не удалось привязать чат');
        }
        return ConnectTelegramResultEnum::Successful;
    }
}
