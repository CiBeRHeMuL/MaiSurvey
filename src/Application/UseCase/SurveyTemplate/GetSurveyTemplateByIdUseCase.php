<?php

namespace App\Application\UseCase\SurveyTemplate;

use App\Domain\Entity\SurveyTemplate;
use App\Domain\Service\SurveyTemplate\SurveyTemplateService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;

class GetSurveyTemplateByIdUseCase
{
    private LoggerInterface $logger;

    public function __construct(
        private SurveyTemplateService $surveyService,
        LoggerInterface $logger,
    ) {
        $this->setLogger($logger);
    }

    public function setLogger(LoggerInterface $logger): GetSurveyTemplateByIdUseCase
    {
        $this->logger = $logger;
        $this->surveyService->setLogger($logger);
        return $this;
    }

    /**
     * @param Uuid $id
     *
     * @return SurveyTemplate|null
     */
    public function execute(Uuid $id): SurveyTemplate|null
    {
        return $this
            ->surveyService
            ->getById($id);
    }
}
