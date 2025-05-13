<?php

namespace App\Application\UseCase\TelegramUser;

use App\Domain\Dto\TelegramUser\ChatId;
use App\Domain\Entity\TelegramUser;
use App\Domain\Enum\ValidationErrorSlugEnum;
use App\Domain\Exception\ValidationException;
use App\Domain\Service\TelegramUser\TelegramUserService;
use App\Domain\Validation\ValidationError;
use Psr\Log\LoggerInterface;
use Throwable;

class GetByChatIdUseCase
{
    public function __construct(
        private LoggerInterface $logger,
        private TelegramUserService $telegramUserService,
    ) {
        $this->setLogger($this->logger);
    }

    public function setLogger(LoggerInterface $logger): GetByChatIdUseCase
    {
        $this->logger = $logger;
        $this->telegramUserService->setLogger($this->logger);
        return $this;
    }

    public function execute(string $chatId): TelegramUser|null
    {
        try {
            $chatId = new ChatId($chatId);
        } catch (Throwable $e) {
            throw ValidationException::new([
                new ValidationError(
                    'chat_id',
                    ValidationErrorSlugEnum::WrongField->getSlug(),
                    'Неверный формат идентификатора чата',
                ),
            ]);
        }

        return $this->telegramUserService->getByChatId($chatId);
    }
}
