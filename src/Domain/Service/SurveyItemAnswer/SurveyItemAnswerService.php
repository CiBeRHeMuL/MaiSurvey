<?php

namespace App\Domain\Service\SurveyItemAnswer;

use App\Domain\Dto\SurveyItem\Choice;
use App\Domain\Dto\SurveyItemAnswer\CreateSurveyItemAnswerDto;
use App\Domain\Entity\SurveyItemAnswer;
use App\Domain\Enum\SurveyItemTypeEnum;
use App\Domain\Enum\ValidationErrorSlugEnum;
use App\Domain\Exception\ValidationException;
use App\Domain\Repository\SurveyItemAnswerRepositoryInterface;
use App\Domain\Service\Db\TransactionManagerInterface;
use App\Domain\Validation\ValidationError;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;
use Throwable;

class SurveyItemAnswerService
{
    private LoggerInterface $logger;

    public function __construct(
        LoggerInterface $logger,
        private TransactionManagerInterface $transactionManager,
        private SurveyItemAnswerRepositoryInterface $surveyItemAnswerRepository,
    ) {
        $this->setLogger($logger);
    }

    public function setLogger(LoggerInterface $logger): SurveyItemAnswerService
    {
        $this->logger = $logger;
        return $this;
    }

    public function validateCreateDto(CreateSurveyItemAnswerDto $dto): void
    {
        if ($dto->getSurveyItem()->getType() !== $dto->getData()->getType()) {
            throw ValidationException::new([
                new ValidationError(
                    'answer.type',
                    ValidationErrorSlugEnum::WrongField->getSlug(),
                    'Неверный тип данных',
                ),
            ]);
        }

        switch ($dto->getSurveyItem()->getType()) {
            case SurveyItemTypeEnum::Choice:
                $choices = $dto->getSurveyItem()->getData()->getChoices();
                $checked = false;
                /** @var Choice $choice */
                foreach ($choices as $choice) {
                    if ($choice->value === $dto->getData()->getChoice()) {
                        $checked = true;
                    }
                }
                if ($checked === false) {
                    throw ValidationException::new([
                        new ValidationError(
                            'answer.choice',
                            ValidationErrorSlugEnum::WrongField->getSlug(),
                            'Выбран несуществующий вариант ответа',
                        ),
                    ]);
                }
                break;
            case SurveyItemTypeEnum::MultiChoice:
                $choices = $dto->getSurveyItem()->getData()->getChoices();
                foreach ($dto->getData()->getChoices() as $k => $checkingChoice) {
                    $checked = false;
                    /** @var Choice $choice */
                    foreach ($choices as $choice) {
                        if ($choice->value === $checkingChoice) {
                            $checked = true;
                        }
                    }
                    if ($checked === false) {
                        throw ValidationException::new([
                            new ValidationError(
                                "answer.choices[$k]",
                                ValidationErrorSlugEnum::WrongField->getSlug(),
                                'Выбран несуществующий вариант ответа',
                            ),
                        ]);
                    }
                }
                break;
            case SurveyItemTypeEnum::Rating:
                if (
                    $dto->getData()->getRating() < $dto->getSurveyItem()->getData()->getMin()
                    || $dto->getData()->getRating() > $dto->getSurveyItem()->getData()->getMax()
                ) {
                    throw ValidationException::new([
                        new ValidationError(
                            'answer.rating',
                            ValidationErrorSlugEnum::WrongField->getSlug(),
                            'Выбран несуществующий вариант ответа',
                        ),
                    ]);
                }
                break;
            default:
                break;
        }
    }

    public function entityFromCreateDto(CreateSurveyItemAnswerDto $dto): SurveyItemAnswer
    {
        $entity = new SurveyItemAnswer();
        $entity
            ->setSurveyItemId($dto->getSurveyItem()->getId())
            ->setTeacherSubjectId($dto->getTeacherSubject()->getId())
            ->setAnswer($dto->getData())
            ->setCreatedAt(new DateTimeImmutable())
            ->setType($dto->getSurveyItem()->getType())
            ->setItem($dto->getSurveyItem())
            ->setTeacherSubject($dto->getTeacherSubject());
        return $entity;
    }

    /**
     * @param CreateSurveyItemAnswerDto[] $dtos
     * @param bool $validate
     * @param bool $transaction
     * @param bool $throwOnError
     *
     * @return int
     * @throws Throwable
     */
    public function createMulti(array $dtos, bool $validate = true, bool $transaction = true, bool $throwOnError = false): int
    {
        if ($dtos === []) {
            return 0;
        }

        if ($transaction) {
            $this->transactionManager->beginTransaction();
        }

        try {
            $entities = [];
            foreach ($dtos as $dto) {
                if ($validate) {
                    $this->validateCreateDto($dto);
                }

                $entities[] = $this->entityFromCreateDto($dto);
            }

            $created = $this->surveyItemAnswerRepository->createMulti($entities);

            if ($transaction) {
                $this->transactionManager->commit();
            }

            return $created;
        } catch (Throwable $e) {
            $this->logger->error($e);
            $this->transactionManager->rollback();
            if ($throwOnError) {
                throw $e;
            } else {
                return 0;
            }
        }
    }
}
