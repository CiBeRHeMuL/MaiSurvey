<?php

namespace App\Application\UseCase\Survey;

use App\Application\Dto\Survey\Create\CreateItemDto;
use App\Application\Dto\Survey\Create\CreateSurveyDto;
use App\Application\Mapper\Survey\ItemDataMapper;
use App\Domain\Dto\Survey\CreateItemDto as DomainCreateItemDto;
use App\Domain\Dto\Survey\CreateSurveyDto as DomainCreateSurveyDto;
use App\Domain\Entity\Survey;
use App\Domain\Enum\SurveyItemTypeEnum;
use App\Domain\Enum\SurveyStatusEnum;
use App\Domain\Enum\TeacherSubjectTypeEnum;
use App\Domain\Enum\ValidationErrorSlugEnum;
use App\Domain\Exception\ValidationException;
use App\Domain\Service\Subject\SubjectService;
use App\Domain\Service\Survey\SurveyService;
use App\Domain\Validation\ValidationError;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;

class CreateSurveyUseCase
{
    private LoggerInterface $logger;

    public function __construct(
        LoggerInterface $logger,
        private SurveyService $surveyService,
        private SubjectService $subjectService,
    ) {
        $this->setLogger($logger);
    }

    public function setLogger(LoggerInterface $logger): CreateSurveyUseCase
    {
        $this->logger = $logger;
        $this->surveyService->setLogger($logger);
        $this->subjectService->setLogger($logger);
        return $this;
    }

    public function execute(CreateSurveyDto $dto): Survey
    {
        $subjectId = new Uuid($dto->subject_id);

        $subject = $this->subjectService->getById($subjectId);
        if ($subject === null) {
            throw ValidationException::new([
                new ValidationError(
                    'subject_id',
                    ValidationErrorSlugEnum::NotFound->getSlug(),
                    'Предмет не найден',
                ),
            ]);
        }

        return $this
            ->surveyService
            ->create(
                new DomainCreateSurveyDto(
                    $dto->title,
                    $dto->actual_to !== null ? new DateTimeImmutable($dto->actual_to) : null,
                    array_map(
                        fn(CreateItemDto $item) => new DomainCreateItemDto(
                            $item->answer_required,
                            SurveyItemTypeEnum::from($item->type),
                            $item->text,
                            $item->position,
                            ItemDataMapper::map($item->data),
                            $item->subject_type !== null
                                ? TeacherSubjectTypeEnum::from($item->subject_type)
                                : null,
                        ),
                        $dto->items,
                    ),
                    $subject,
                    SurveyStatusEnum::from($dto->status),
                ),
            );
    }
}
