<?php

namespace App\Infrastructure\Messenger\Handler;

use App\Domain\Exception\ErrorException;
use App\Domain\Service\Survey\SurveyService;
use App\Domain\Service\SurveyStat\SurveyStatService;
use App\Infrastructure\Messenger\Message\RefreshStatMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Throwable;

#[AsMessageHandler]
class RefreshStatHandler
{
    private LoggerInterface $logger;

    public function __construct(
        LoggerInterface $logger,
        private SurveyStatService $surveyStatService,
        private SurveyService $surveyService,
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

    public function __invoke(RefreshStatMessage $message): void
    {
        $survey = $this->surveyService->getById($message->getSurveyId());
        if ($survey === null) {
            throw ErrorException::new("Не удалось найти опрос {$message->getSurveyId()->toRfc4122()} для обновления статистики");
        }
        try {
            $this->surveyStatService->refreshStat($survey);
        } catch (Throwable $e) {
            $this->logger->error($e);
            throw ErrorException::new("Не удалось обновить статистику по опросу {$message->getSurveyId()->toRfc4122()}");
        }
    }
}
