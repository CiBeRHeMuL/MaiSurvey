<?php

namespace App\Presentation\Console\Command;

use AndrewGos\TelegramBot\Request\SetWebhookRequest;
use AndrewGos\TelegramBot\Telegram;
use AndrewGos\TelegramBot\ValueObject\Url;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AsCommand('telegram:set-webhook')]
class TelegramSetWebhookCommand extends AbstractCommand
{
    public function __construct(
        private Telegram $telegram,
        private UrlGeneratorInterface $urlGenerator,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        parent::configure();
        $this->setDescription('Устанавливает webhook для бота');;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->info('Устанавливаем webhook...');

        $url = $this->urlGenerator->generate('telegram-webhook', referenceType: UrlGeneratorInterface::ABSOLUTE_URL);
        $this->io->info(sprintf('URL: %s', $url));

        $response = $this->telegram->getApi()->setWebhook(
            new SetWebhookRequest(
                new Url($url),
            ),
        );

        if ($response->isOk()) {
            $this->io->success('Webhook успешно установлен');
            return self::SUCCESS;
        } else {
            $this->io->error(
                sprintf(
                    "Ошибка установки webhook: %s\n\n%s",
                    $response->getDescription(),
                    json_encode($response->getParameters()?->toArray()),
                ),
            );
            return self::FAILURE;
        }
    }
}
