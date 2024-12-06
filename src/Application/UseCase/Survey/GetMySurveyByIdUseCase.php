<?php

namespace App\Application\UseCase\Survey;

use App\Domain\Dto\Survey\GetMySurveyByIdDto;
use App\Domain\Entity\MySurvey;
use App\Domain\Entity\User;
use App\Domain\Service\Survey\SurveyService;
use Psr\Log\LoggerInterface;

class GetMySurveyByIdUseCase
{
    private LoggerInterface $logger;

    public function __construct(
        private SurveyService $surveyService,
        LoggerInterface $logger,
    ) {
        $this->setLogger($logger);
    }

    public function setLogger(LoggerInterface $logger): GetMySurveyByIdUseCase
    {
        $this->logger = $logger;
        $this->surveyService->setLogger($logger);
        return $this;
    }

    /**
     * @param User $user
     * @param GetMySurveyByIdDto $dto
     *
     * @return MySurvey|null
     */
    public function execute(User $user, GetMySurveyByIdDto $dto): MySurvey|null
    {
        return $this
            ->surveyService
            ->getMyById($user, $dto);
    }
}
