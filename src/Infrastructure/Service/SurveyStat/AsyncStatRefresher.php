<?php

namespace App\Infrastructure\Service\SurveyStat;

use App\Domain\Entity\Survey;
use App\Domain\Exception\ErrorException;
use App\Domain\Service\SurveyStat\StatRefresherInterface;
use App\Infrastructure\Messenger\Message\RefreshStatsMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Throwable;

class AsyncStatRefresher implements StatRefresherInterface
{
    private LoggerInterface $logger;

    public function __construct(
        LoggerInterface $logger,
        private MessageBusInterface $messageBus,
    ) {
        $this->setLogger($logger);
    }

    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    public function setLogger(LoggerInterface $logger): static
    {
        $this->logger = $logger;
        return $this;
    }

    public function refreshStats(array|null $surveys = null): void
    {
        try {
            if ($surveys !== []) {
                $this->messageBus->dispatch(
                    new RefreshStatsMessage(
                        $surveys !== null
                            ? array_map(fn(Survey $s) => $s->getId(), $surveys)
                            : null,
                    ),
                );
            }
        } catch (Throwable $e) {
            $this->logger->error($e);
            throw ErrorException::new('Не удалось пройти опрос');
        }
    }
}
