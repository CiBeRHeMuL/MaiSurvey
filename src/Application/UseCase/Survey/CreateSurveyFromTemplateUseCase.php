<?php

namespace App\Application\UseCase\Survey;

use App\Application\Dto\Survey\Create\CreateFromTemplateDto;
use App\Domain\Dto\Survey\CreateSurveyFromTemplateDto;
use App\Domain\Entity\Survey;
use App\Domain\Enum\SurveyStatusEnum;
use App\Domain\Enum\ValidationErrorSlugEnum;
use App\Domain\Exception\ValidationException;
use App\Domain\Service\Subject\SubjectService;
use App\Domain\Service\Survey\SurveyService;
use App\Domain\Service\SurveyTemplate\SurveyTemplateService;
use App\Domain\Validation\ValidationError;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;

/**
 * Создает опрос из шаблона
 */
class CreateSurveyFromTemplateUseCase
{
    private LoggerInterface $logger;

    public function __construct(
        LoggerInterface $logger,
        private SurveyService $surveyService,
        private SubjectService $subjectService,
        private SurveyTemplateService $surveyTemplateService,
    ) {
        $this->setLogger($logger);
    }

    public function setLogger(LoggerInterface $logger): self
    {
        $this->logger = $logger;
        $this->surveyService->setLogger($logger);
        $this->subjectService->setLogger($logger);
        $this->surveyTemplateService->setLogger($logger);
        return $this;
    }

    public function execute(CreateFromTemplateDto $dto): Survey
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

        $templateId = new Uuid($dto->template_id);
        $template = $this->surveyTemplateService->getById($templateId);
        if ($template === null) {
            throw ValidationException::new([
                new ValidationError(
                    'template_id',
                    ValidationErrorSlugEnum::NotFound->getSlug(),
                    'Шаблон не найден',
                ),
            ]);
        }

        return $this
            ->surveyService
            ->createFromTemplate(
                new CreateSurveyFromTemplateDto(
                    $subject,
                    $dto->actual_to !== null ? new DateTimeImmutable($dto->actual_to) : null,
                    $template,
                    SurveyStatusEnum::from($dto->status),
                ),
            );
    }
}
