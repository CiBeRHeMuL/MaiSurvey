<?php

namespace App\Application\UseCase\Survey;

use App\Application\Dto\Survey\Complete\CompleteSurveyDto;
use App\Application\Dto\Survey\Complete\CompleteSurveyItemDto;
use App\Application\Mapper\Survey\AnswerDataMapper;
use App\Domain\Dto\Survey\Complete\CompleteSurveyDto as DomainCompleteSurveyDto;
use App\Domain\Dto\Survey\Complete\CompleteSurveyItemDto as DomainCompleteSurveyItemDto;
use App\Domain\Entity\User;
use App\Domain\Service\Survey\CompleteSurveyService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;

class CompleteSurveyUseCase
{
    private LoggerInterface $logger;

    public function __construct(
        LoggerInterface $logger,
        private CompleteSurveyService $completeSurveyService,
    ) {
        $this->setLogger($logger);
    }

    public function setLogger(LoggerInterface $logger): CompleteSurveyUseCase
    {
        $this->logger = $logger;
        $this->completeSurveyService->setLogger($logger);
        return $this;
    }

    public function execute(User $user, CompleteSurveyDto $dto): void
    {
        $this
            ->completeSurveyService
            ->complete(
                $user,
                new DomainCompleteSurveyDto(
                    $dto->id,
                    array_map(
                        fn(CompleteSurveyItemDto $item) => new DomainCompleteSurveyItemDto(
                            new Uuid($item->id),
                            AnswerDataMapper::map($item->data),
                        ),
                        $dto->answers,
                    ),
                ),
            );
    }
}
