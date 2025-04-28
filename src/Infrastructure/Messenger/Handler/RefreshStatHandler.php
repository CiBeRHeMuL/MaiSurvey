<?php

namespace App\Infrastructure\Messenger\Handler;

use App\Application\Dto\Survey\GetSurveysDto;
use App\Application\UseCase\Survey\GetSurveysByIdsUseCase;
use App\Application\UseCase\Survey\GetSurveysUseCase;
use App\Application\UseCase\SurveyStat\GenerateForSurveysUseCase;
use App\Domain\Entity\Survey;
use App\Domain\Exception\ErrorException;
use App\Infrastructure\Messenger\Message\RefreshStatsMessage;
use DateTimeImmutable;
use Psr\Cache\CacheItemInterface;
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
        $this->logger->warning(sprintf('Given surveys: %s', implode(', ', $message->getSurveyIds())));
        $surveys = [];
        if ($message->getSurveyIds() !== null) {
            $surveys = $this->surveysByIdsUseCase->execute(
                $message->getSurveyIds(),
                $message->isForce() === false,
            );
        } else {
            $surveys = iterator_to_array(
                $this
                    ->surveysUseCase
                    ->execute(new GetSurveysDto())
                    ->getItems(),
            );
        }
        if ($surveys === []) {
            return;
        }
        $surveys = array_filter(
            $surveys,
            function (Survey $s) use (&$message) {
                $lastRefreshTimeKey = $this->getLastRefreshTimeCacheKey($s);
                /** @var DateTimeImmutable|null $lastRefreshTime */
                $lastRefreshTime = $this
                    ->cache
                    ->get(
                        $lastRefreshTimeKey,
                        function (CacheItemInterface $item, bool &$save) {
                            $save = false;
                            return null;
                        },
                    );
                return $lastRefreshTime === null
                    || $message->getRefreshTime()->getTimestamp() > $lastRefreshTime->getTimestamp();
            },
        );
        if ($surveys === []) {
            return;
        }
        $this->logger->warning(sprintf('Found %d surveys', count($surveys)));

        try {
            $refreshed = $this->generateForSurveysUseCase->execute($surveys, $message->isForce());
            $this->logger->info(sprintf('Статистка успешно обновлена для %d опросов', $refreshed));
            $this->logger->warning(sprintf('Статистка успешно обновлена для %d опросов', $refreshed));
            foreach ($surveys as $survey) {
                $this
                    ->cache
                    ->get(
                        $this->getLastRefreshTimeCacheKey($survey),
                        function (CacheItemInterface $item, bool &$save) {
                            $save = true;
                            return new DateTimeImmutable();
                        },
                    );
            }
        } catch (Throwable $e) {
            $this->logger->error($e);
            throw ErrorException::new('Не удалось обновить статистику по опросам');
        }
    }

    private function getLastRefreshTimeCacheKey(Survey $s): string
    {
        return "slrt_{$s->getId()->toRfc4122()}";
    }
}
