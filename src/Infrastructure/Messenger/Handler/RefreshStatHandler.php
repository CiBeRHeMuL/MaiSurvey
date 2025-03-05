<?php

namespace App\Infrastructure\Messenger\Handler;

use App\Domain\Dto\Survey\GetSurveysDto;
use App\Domain\Entity\Survey;
use App\Domain\Exception\ErrorException;
use App\Domain\Service\Survey\SurveyService;
use App\Domain\Service\SurveyStat\SurveyStatService;
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
        private SurveyStatService $surveyStatService,
        private SurveyService $surveyService,
        private CacheInterface $cache,
    ) {
        $this->setLogger($logger);
    }

    public function setLogger(LoggerInterface $logger): RefreshStatHandler
    {
        $this->logger = $logger;
        $this->surveyStatService->setLogger($logger);
        $this->surveyService->setLogger($logger);
        return $this;
    }

    public function __invoke(RefreshStatsMessage $message): void
    {
        $surveys = [];
        if ($message->getSurveyIds() !== null) {
            $surveys = $this->surveyService->getByIds($message->getSurveyIds());
        } else {
            $surveys = iterator_to_array(
                $this
                    ->surveyService
                    ->getAll(new GetSurveysDto())
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

        try {
            $this->surveyStatService->refreshStats($surveys);
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
