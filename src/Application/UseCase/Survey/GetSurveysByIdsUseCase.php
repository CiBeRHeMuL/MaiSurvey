<?php

namespace App\Application\UseCase\Survey;

use App\Domain\Entity\Survey;
use App\Domain\Service\Survey\SurveyService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;

class GetSurveysByIdsUseCase
{
    private LoggerInterface $logger;

    public function __construct(
        private SurveyService $surveyService,
        LoggerInterface $logger,
    ) {
        $this->setLogger($logger);
    }

    public function setLogger(LoggerInterface $logger): GetSurveysByIdsUseCase
    {
        $this->logger = $logger;
        $this->surveyService->setLogger($logger);
        return $this;
    }

    /**
     * @param Uuid[] $ids
     * @param bool|null $actual
     *
     * @return Survey[]
     */
    public function execute(array $ids, bool|null $actual = null): array
    {
        return $this
            ->surveyService
            ->getByIds($ids, $actual);
    }
}
