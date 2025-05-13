<?php

namespace App\Infrastructure\Messenger\Handler;

use App\Application\Dto\Survey\GetSurveysDto;
use App\Application\UseCase\Survey\GetSurveysByIdsUseCase;
use App\Application\UseCase\Survey\GetSurveysUseCase;
use App\Application\UseCase\SurveyStat\GenerateForSurveysUseCase;
use App\Domain\Entity\Survey;
use App\Domain\Exception\ErrorException;
use App\Domain\Validation\ValidationError;
use App\Infrastructure\Messenger\Message\RefreshStatsMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\Cache\CacheInterface;
use Throwable;

#[AsMessageHandler]
class RefreshStatHandler
{
    private LoggerInterface $logger;

    public function __construct(
        LoggerInterface $logger,
        private CacheInterface $cache,
        private GetSurveysByIdsUseCase $surveysByIdsUseCase,
        private GetSurveysUseCase $surveysUseCase,
        private GenerateForSurveysUseCase $generateForSurveysUseCase,
    ) {
        $this->setLogger($logger);
    }

    public function setLogger(LoggerInterface $logger): RefreshStatHandler
    {
        $this->logger = $logger;
        $this->surveysByIdsUseCase->setLogger($logger);
        $this->surveysUseCase->setLogger($logger);
        $this->generateForSurveysUseCase->setLogger($logger);
        return $this;
    }

    public function __invoke(RefreshStatsMessage $message): void
    {
        $surveys = [];
        if ($message->getSurveyIds() !== null) {
            $surveys = $this->surveysByIdsUseCase->execute(
                $message->getSurveyIds(),
                $message->isForce() ? null : true,
            );
        } else {
            $surveys = iterator_to_array(
                $this->surveysUseCase
                    ->execute(new GetSurveysDto(limit: null, actual: $message->isForce() ? null : true))
                    ->getItems(),
            );
        }
        if ($surveys === []) {
            return;
        }

        try {
            $refreshed = $this->generateForSurveysUseCase->execute($surveys, $message->isForce());
            $this->logger->info(sprintf('Статистка успешно обновлена для %d опросов', $refreshed));
        } catch (Throwable $e) {
            if (!$e instanceof ValidationError && !$e instanceof ErrorException) {
                $this->logger->error('An error occurred', ['exception' => $e]);
            }
            throw ErrorException::new('Не удалось обновить статистику по опросам');
        }
    }

    private function getLastRefreshTimeCacheKey(Survey $s): string
    {
        return "slrt_{$s->getId()->toRfc4122()}";
    }
}
