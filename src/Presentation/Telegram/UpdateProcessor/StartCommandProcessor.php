<?php

namespace App\Presentation\Telegram\UpdateProcessor;

use AndrewGos\TelegramBot\Enum\ChatTypeEnum;
use AndrewGos\TelegramBot\Request\SendMessageRequest;
use AndrewGos\TelegramBot\UpdateHandler\UpdateProcessor\AbstractCommandMessageUpdateProcessor;
use AndrewGos\TelegramBot\ValueObject\ChatId;
use App\Application\Dto\TelegramUser\CreateTelegramUserDto;
use App\Application\UseCase\TelegramUser\CreateTelegramUserUseCase;
use App\Application\UseCase\TelegramUser\GetByChatIdUseCase;
use App\Domain\Enum\ConnectTelegramResultEnum;
use Throwable;

class StartCommandProcessor extends AbstractCommandMessageUpdateProcessor
{
    use AuthUpdateProcessorTrait;

    public function __construct(
        private GetByChatIdUseCase $getByChatIdUseCase,
        private CreateTelegramUserUseCase $createTelegramUserUseCase,
    ) {
    }

    public function beforeProcess(): bool
    {
        $chatId = new ChatId($this->message->getChat()->getId());
        if ($this->message->getChat()->getType() !== ChatTypeEnum::Private) {
            $this->getApi()->sendMessage(
                new SendMessageRequest(
                    $chatId,
                    'Бот доступен для использования только в личных чатах!',
                ),
            );
            return false;
        }
        if ($this->authenticate($chatId)) {
            $this->getApi()->sendMessage(
                new SendMessageRequest(
                    $chatId,
                    'Ты уже привязал свой Telegram к профилю!',
                ),
            );
            return false;
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function process(): void
    {
        $connectId = $this->textWithoutCommand;
        if (!$connectId) {
            $this->getApi()->sendMessage(
                new SendMessageRequest(
                    new ChatId($this->message->getChat()->getId()),
                    'Кажется, ты перешел по неправильной ссылке. Для подключения Telegram к профилю перейди по ссылке с сайта!',
                ),
            );
            return;
        }

        try {
            $result = $this->createTelegramUserUseCase->execute(
                new CreateTelegramUserDto(
                    $connectId,
                    (string)$this->message->getChat()->getId(),
                ),
            );
            $message = match ($result) {
                ConnectTelegramResultEnum::Successful => 'Аккаунт успешно привязан!',
                ConnectTelegramResultEnum::ConnectedToAnother => 'Этот аккаунт уже привязан к другому профилю!',
                ConnectTelegramResultEnum::AlreadyConnected => 'Ты уже привязал свой Telegram к профилю!',
            };

            $this->getApi()->sendMessage(
                new SendMessageRequest(
                    new ChatId($this->message->getChat()->getId()),
                    $message,
                ),
            );
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
            $this->getApi()->sendMessage(
                new SendMessageRequest(
                    new ChatId($this->message->getChat()->getId()),
                    'Кажется, что-то пошло не так. Попробуй еще раз или обратись в поддержку',
                ),
            );
        }
    }
}
