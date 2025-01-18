<?php

namespace App\Application\UseCase\Survey;

use App\Application\Dto\Survey\Create\CreateSurveyDto;
use App\Domain\Entity\Survey;
use App\Domain\Exception\ValidationException;
use App\Domain\Service\Db\TransactionManagerInterface;
use App\Domain\Validation\ValidationError;
use Psr\Log\LoggerInterface;
use Throwable;

class CreateSurveysUseCase
{
    private LoggerInterface $logger;

    public function __construct(
        LoggerInterface $logger,
        private TransactionManagerInterface $transactionManager,
        private CreateSurveyUseCase $useCase,
    ) {
        $this->setLogger($logger);
    }

    public function setLogger(LoggerInterface $logger): CreateSurveysUseCase
    {
        $this->logger = $logger;
        $this->useCase->setLogger($logger);
        return $this;
    }

    /**
     * @param CreateSurveyDto[] $dtos
     *
     * @return Survey[]
     */
    public function execute(array $dtos): array
    {
        $surveys = [];
        $this->transactionManager->beginTransaction();
        foreach ($dtos as $k => $dto) {
            try {
                $surveys[] = $this->useCase->execute($dto);
            } catch (ValidationException $e) {
                $this->transactionManager->rollback();
                throw ValidationException::new(
                    array_map(
                        fn(ValidationError $er) => new ValidationError(
                            "[$k].{$er->getField()}",
                            $er->getSlug(),
                            $er->getMessage(),
                        ),
                        $e->getErrors(),
                    ),
                );
            } catch (Throwable $e) {
                $this->transactionManager->rollback();
                throw $e;
            }
        }
        $this->transactionManager->commit();
        return $surveys;
    }
}
