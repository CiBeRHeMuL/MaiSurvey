<?php

namespace App\Application\UseCase\Survey;

use App\Application\Dto\Survey\Create\CreateItemDto;
use App\Application\Dto\Survey\Update\UpdateItemDto;
use App\Application\Dto\Survey\Update\UpdateSurveyDto;
use App\Application\Mapper\Survey\ItemDataMapper;
use App\Domain\Dto\Survey\UpdateItemDto as DomainUpdateItemDto;
use App\Domain\Dto\Survey\CreateItemDto as DomainCreateItemDto;
use App\Domain\Dto\Survey\UpdateSurveyDto as DomainUpdateSurveyDto;
use App\Domain\Entity\Survey;
use App\Domain\Enum\SurveyItemTypeEnum;
use App\Domain\Enum\SurveyStatusEnum;
use App\Domain\Enum\TeacherSubjectTypeEnum;
use App\Domain\Enum\ValidationErrorSlugEnum;
use App\Domain\Exception\ErrorException;
use App\Domain\Exception\ValidationException;
use App\Domain\Service\Subject\SubjectService;
use App\Domain\Service\Survey\SurveyService;
use App\Domain\Validation\ValidationError;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;

class UpdateSurveyUseCase
{
    private LoggerInterface $logger;

    public function __construct(
        LoggerInterface $logger,
        private SurveyService $surveyService,
        private SubjectService $subjectService,
    ) {
        $this->setLogger($logger);
    }

    public function setLogger(LoggerInterface $logger): UpdateSurveyUseCase
    {
        $this->logger = $logger;
        $this->surveyService->setLogger($logger);
        $this->subjectService->setLogger($logger);
        return $this;
    }

    public function execute(Uuid $id, UpdateSurveyDto $dto): Survey
    {
        $survey = $this->surveyService->getById($id);
        if ($survey === null) {
            throw ErrorException::new('Опрос не найден', 404);
        }

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
            ->update(
                $survey,
                new DomainUpdateSurveyDto(
                    $dto->title,
                    $dto->actual_to !== null ? new DateTimeImmutable($dto->actual_to) : null,
                    array_map(
                        function (UpdateItemDto $item) {
                            if ($item->id !== null) {
                                return new DomainUpdateItemDto(
                                    new Uuid($item->id),
                                    $item->answer_required,
                                    SurveyItemTypeEnum::from($item->type),
                                    $item->text,
                                    $item->position,
                                    ItemDataMapper::map($item->data),
                                    $item->subject_type !== null
                                        ? TeacherSubjectTypeEnum::from($item->subject_type)
                                        : null,
                                );
                            } else {
                                return new DomainCreateItemDto(
                                    $item->answer_required,
                                    SurveyItemTypeEnum::from($item->type),
                                    $item->text,
                                    $item->position,
                                    ItemDataMapper::map($item->data),
                                    $item->subject_type !== null
                                        ? TeacherSubjectTypeEnum::from($item->subject_type)
                                        : null,
                                );
                            }

                        },
                        $dto->items,
                    ),
                    $subject,
                    SurveyStatusEnum::from($dto->status),
                ),
            );
    }
}
