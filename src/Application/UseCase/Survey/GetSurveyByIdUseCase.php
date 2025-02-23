<?php

namespace App\Application\UseCase\Survey;

use App\Domain\Entity\Survey;
use App\Domain\Service\Survey\SurveyService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;

class GetSurveyByIdUseCase
{
    private LoggerInterface $logger;

    public function __construct(
        private SurveyService $surveyService,
        LoggerInterface $logger,
    ) {
        $this->setLogger($logger);
    }

    public function setLogger(LoggerInterface $logger): GetSurveyByIdUseCase
    {
        $this->logger = $logger;
        $this->surveyService->setLogger($logger);
        return $this;
    }

    /**
     * @param Uuid $id
     *
     * @return Survey|null
     */
    public function execute(Uuid $id): Survey|null
    {
        return $this
            ->surveyService
            ->getById($id);
    }
}
