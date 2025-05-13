<?php

namespace App\Application\UseCase\Survey;

use App\Application\Dto\Survey\Create\CreateSurveyDto;
use App\Application\Dto\Survey\Create\CreateSurveyMSDto;
use App\Domain\Entity\Survey;
use App\Domain\Exception\ErrorException;
use App\Domain\Exception\ValidationException;
use App\Domain\Service\Db\TransactionManagerInterface;
use App\Domain\Service\Subject\SubjectService;
use App\Domain\Service\Survey\SurveyService;
use App\Domain\Validation\ValidationError;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Создает опрос для нескольких предметов сразу
 */
class CreateSurveyMSUseCase
{
    private LoggerInterface $logger;

    public function __construct(
        LoggerInterface $logger,
        private SurveyService $surveyService,
        private SubjectService $subjectService,
        private TransactionManagerInterface $transactionManager,
        private CreateSurveyUseCase $useCase,
    ) {
        $this->setLogger($logger);
    }

    public function setLogger(LoggerInterface $logger): self
    {
        $this->logger = $logger;
        $this->surveyService->setLogger($logger);
        $this->subjectService->setLogger($logger);
        $this->useCase->setLogger($logger);
        return $this;
    }

    /**
     * @param CreateSurveyMSDto $dto
     *
     * @return Survey[]
     */
    public function execute(CreateSurveyMSDto $dto): array
    {
        $surveys = [];
        $this->transactionManager->beginTransaction();
        foreach ($dto->subject_ids as $k => $subjectId) {
            try {
                $surveys[] = $this->useCase->execute(
                    new CreateSurveyDto(
                        $dto->title,
                        $subjectId,
                        $dto->items,
                        $dto->status,
                        $dto->actual_to,
                    ),
                );
            } catch (ValidationException $e) {
                $errors = $e->getErrors();
                foreach ($errors as &$error) {
                    if ($error->getField() === 'subject_id') {
                        $error = new ValidationError(
                            "subject_ids[$k]",
                            $error->getSlug(),
                            $error->getMessage(),
                        );
                    }
                }
                $this->transactionManager->rollback();
                throw ValidationException::new($errors);
            } catch (ErrorException $e) {
                $this->transactionManager->rollback();
                throw $e;
            } catch (Throwable $e) {
                $this->logger->error('An error occurred', ['exception' => $e]);
                $this->transactionManager->rollback();
                throw ErrorException::new('Не удалось создать опросы, обратитесь в поддержку');
            }
        }
        $this->transactionManager->commit();
        return $surveys;
    }
}
