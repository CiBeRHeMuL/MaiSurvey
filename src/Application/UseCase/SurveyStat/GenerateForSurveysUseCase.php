<?php

namespace App\Application\UseCase\SurveyStat;

use App\Domain\Entity\Survey;
use App\Domain\Service\SurveyStat\StatRefresherInterface;
use Psr\Log\LoggerInterface;
use Throwable;

class GenerateForSurveysUseCase
{
    private LoggerInterface $logger;

    public function __construct(
        LoggerInterface $logger,
        private StatRefresherInterface $statRefresher,
    ) {
        $this->setLogger($logger);
    }

    public function setLogger(LoggerInterface $logger): GenerateForSurveysUseCase
    {
        $this->logger = $logger;
        $this->statRefresher->setLogger($logger);
        return $this;
    }

    /**
     * @param Survey[]|null $surveys
     * @param bool $force обновить все опросы принудительно
     *
     * @return void
     * @throws Throwable
     */
    public function execute(array|null $surveys = null, bool $force = false): void
    {
        $this
            ->statRefresher
            ->refreshStats($surveys, $force);
    }
}
