<?php

namespace App\Application\UseCase\Survey;

use App\Application\Dto\Survey\Create\CreateItemDto;
use App\Application\Dto\Survey\Create\CreateSurveyDto;
use App\Application\Mapper\Survey\ItemDataMapper;
use App\Domain\Dto\Survey\CreateItemDto as DomainCreateItemDto;
use App\Domain\Dto\Survey\CreateSurveyDto as DomainCreateSurveyDto;
use App\Domain\Entity\Survey;
use App\Domain\Enum\SurveyItemTypeEnum;
use App\Domain\Enum\TeacherSubjectTypeEnum;
use App\Domain\Service\Survey\SurveyService;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;

class CreateSurveyUseCase
{
    private LoggerInterface $logger;

    public function __construct(
        LoggerInterface $logger,
        private SurveyService $surveyService,
    ) {
        $this->setLogger($logger);
    }

    public function setLogger(LoggerInterface $logger): CreateSurveyUseCase
    {
        $this->logger = $logger;
        $this->surveyService->setLogger($logger);
        return $this;
    }

    public function execute(CreateSurveyDto $dto): Survey
    {
        return $this
            ->surveyService
            ->create(
                new DomainCreateSurveyDto(
                    $dto->title,
                    new Uuid($dto->subject_id),
                    new DateTimeImmutable($dto->actual_to),
                    array_map(
                        fn(CreateItemDto $item) => new DomainCreateItemDto(
                            $item->answer_required,
                            SurveyItemTypeEnum::from($item->type),
                            $item->text,
                            $item->position,
                            ItemDataMapper::map($item->data),
                            TeacherSubjectTypeEnum::from($item->subject_type),
                        ),
                        $dto->items,
                    ),
                ),
            );
    }
}
