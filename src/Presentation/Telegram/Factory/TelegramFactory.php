<?php

namespace App\Presentation\Telegram\Factory;

use AndrewGos\ClassBuilder\ClassBuilder;
use AndrewGos\TelegramBot\Api\Api;
use AndrewGos\TelegramBot\Filesystem\Filesystem;
use AndrewGos\TelegramBot\Http\Client\HttpClient;
use AndrewGos\TelegramBot\Http\Factory\TelegramRequestFactory;
use AndrewGos\TelegramBot\Telegram;
use AndrewGos\TelegramBot\UpdateHandler\UpdateHandler;
use AndrewGos\TelegramBot\UpdateHandler\UpdateHandlerInterface;
use AndrewGos\TelegramBot\UpdateHandler\UpdateSource\PhpInputUpdateSource;
use AndrewGos\TelegramBot\ValueObject\BotToken;
use App\Presentation\Telegram\UpdateHandler\AsyncUpdateHandler;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class TelegramFactory
{
    public static function createAsync(BotToken $token, MessageBusInterface $messageBus, LoggerInterface $logger): Telegram
    {
        $classBuilder = new ClassBuilder();
        $api = new Api(
            $token,
            $classBuilder,
            new TelegramRequestFactory(),
            new HttpClient(),
            $logger,
            new Filesystem(),
        );
        return new Telegram(
            $token,
            $api,
            new AsyncUpdateHandler($messageBus, new PhpInputUpdateSource($classBuilder), $api, $logger),
        );
    }
    public static function createSyncHandler(BotToken $token, LoggerInterface $logger): UpdateHandlerInterface
    {
        $classBuilder = new ClassBuilder();
        $api = new Api(
            $token,
            $classBuilder,
            new TelegramRequestFactory(),
            new HttpClient(),
            $logger,
            new Filesystem(),
        );
        return new UpdateHandler(new PhpInputUpdateSource($classBuilder), $api, $logger);
    }
}
