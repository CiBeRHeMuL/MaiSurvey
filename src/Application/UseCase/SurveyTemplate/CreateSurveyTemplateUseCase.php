<?php

namespace App\Application\UseCase\SurveyTemplate;

use App\Application\Dto\SurveyTemplate\Create\CreateSurveyTemplateDto;
use App\Application\Dto\SurveyTemplate\Create\CreateTemplateItemDto;
use App\Application\Mapper\Survey\ItemDataMapper;
use App\Domain\Dto\SurveyTemplate\CreateSurveyTemplateDto as DomainCreateSurveyTemplateDto;
use App\Domain\Dto\SurveyTemplate\CreateTemplateItemDto as DomainCreateItemDto;
use App\Domain\Entity\SurveyTemplate;
use App\Domain\Enum\SurveyItemTypeEnum;
use App\Domain\Enum\TeacherSubjectTypeEnum;
use App\Domain\Service\Semester\SemesterService;
use App\Domain\Service\Subject\SubjectService;
use App\Domain\Service\SurveyTemplate\SurveyTemplateService;
use Psr\Log\LoggerInterface;

class CreateSurveyTemplateUseCase
{
    private LoggerInterface $logger;

    public function __construct(
        LoggerInterface $logger,
        private SurveyTemplateService $surveyService,
        private SubjectService $subjectService,
        private SemesterService $semesterService,
    ) {
        $this->setLogger($logger);
    }

    public function setLogger(LoggerInterface $logger): CreateSurveyTemplateUseCase
    {
        $this->logger = $logger;
        $this->surveyService->setLogger($logger);
        $this->semesterService->setLogger($logger);
        $this->subjectService->setLogger($logger);
        return $this;
    }

    public function execute(CreateSurveyTemplateDto $dto): SurveyTemplate
    {
        return $this
            ->surveyService
            ->create(
                new DomainCreateSurveyTemplateDto(
                    $dto->title,
                    $dto->name,
                    array_map(
                        fn(CreateTemplateItemDto $item) => new DomainCreateItemDto(
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
                ),
            );
    }
}
