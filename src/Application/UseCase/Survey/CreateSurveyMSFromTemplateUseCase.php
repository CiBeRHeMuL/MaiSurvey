<?php

namespace App\Application\UseCase\Survey;

use App\Application\Dto\Survey\Create\CreateMSFromTemplateDto;
use App\Domain\Dto\Survey\CreateSurveyFromTemplateDto;
use App\Domain\Entity\Survey;
use App\Domain\Enum\ValidationErrorSlugEnum;
use App\Domain\Exception\ErrorException;
use App\Domain\Exception\ValidationException;
use App\Domain\Service\Db\TransactionManagerInterface;
use App\Domain\Service\Subject\SubjectService;
use App\Domain\Service\Survey\SurveyService;
use App\Domain\Service\SurveyTemplate\SurveyTemplateService;
use App\Domain\Validation\ValidationError;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;
use Throwable;

/**
 * Создает опрос из шаблона для нескольких предметов сразу
 */
class CreateSurveyMSFromTemplateUseCase
{
    private LoggerInterface $logger;

    public function __construct(
        LoggerInterface $logger,
        private SurveyService $surveyService,
        private SubjectService $subjectService,
        private SurveyTemplateService $surveyTemplateService,
        private TransactionManagerInterface $transactionManager,
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

    /**
     * @param CreateMSFromTemplateDto $dto
     *
     * @return Survey[]
     */
    public function execute(CreateMSFromTemplateDto $dto): array
    {
        if ($dto->subject_ids === []) {
            throw ValidationException::new([
                new ValidationError(
                    "subject_ids",
                    ValidationErrorSlugEnum::WrongField->getSlug(),
                    'Нужно указать хотя бы один предмет',
                ),
            ]);
        }
        $subjects = [];
        foreach ($dto->subject_ids as $k => $subjectId) {
            $subjectId = new Uuid($subjectId);
            $subject = $this->subjectService->getById($subjectId);
            if ($subject === null) {
                throw ValidationException::new([
                    new ValidationError(
                        "subject_ids[$k]",
                        ValidationErrorSlugEnum::NotFound->getSlug(),
                        'Предмет не найден',
                    ),
                ]);
            }
            $subjects[] = $subject;
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

        $surveys = [];
        $this->transactionManager->beginTransaction();
        foreach ($subjects as $subject) {
            try {
                $surveys[] = $this->surveyService->createFromTemplate(
                    new CreateSurveyFromTemplateDto(
                        $subject,
                        new DateTimeImmutable($dto->actual_to),
                        $template,
                    ),
                );
            } catch (ValidationException|ErrorException $e) {
                $this->transactionManager->rollback();
                throw $e;
            } catch (Throwable $e) {
                $this->logger->error($e);
                $this->transactionManager->rollback();
                throw ErrorException::new('Не удалось создать опросы из шаблона, обратитесь в поддержку');
            }
        }
        $this->transactionManager->commit();
        return $surveys;
    }
}
