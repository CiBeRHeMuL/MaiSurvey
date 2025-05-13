<?php

namespace App\Infrastructure\Messenger\Handler;

use App\Application\UseCase\Survey\CloseExpiredSurveysUseCase;
use App\Domain\Exception\ErrorException;
use App\Domain\Validation\ValidationError;
use App\Infrastructure\Messenger\Message\CloseExpiredSurveysMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Throwable;

#[AsMessageHandler]
class CloseExpiredSurveysHandler
{
    private LoggerInterface $logger;

    public function __construct(
        LoggerInterface $logger,
        private CloseExpiredSurveysUseCase $useCase,
    ) {
        $this->setLogger($logger);
    }

    public function setLogger(LoggerInterface $logger): CloseExpiredSurveysHandler
    {
        $this->logger = $logger;
        $this->useCase->setLogger($logger);
        return $this;
    }

    public function __invoke(CloseExpiredSurveysMessage $message): void
    {
        try {
            $closed = $this->useCase->execute();
            $this->logger->info(sprintf('Закрыто %d истекших опросов', $closed));
        } catch (Throwable $e) {
            if (!$e instanceof ValidationError && !$e instanceof ErrorException) {
                $this->logger->error('An error occurred', ['exception' => $e]);
            }
        }
    }
}
