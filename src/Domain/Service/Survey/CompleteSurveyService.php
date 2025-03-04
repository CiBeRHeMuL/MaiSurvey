<?php

namespace App\Domain\Service\Survey;

use App\Domain\Dto\CompletedSurvey\CreateCompletedSurveyDto;
use App\Domain\Dto\Survey\Complete\CompleteSurveyDto;
use App\Domain\Dto\Survey\Complete\CompleteSurveyItemDto;
use App\Domain\Dto\Survey\GetMySurveyByIdDto;
use App\Domain\Dto\SurveyItemAnswer\CreateSurveyItemAnswerDto;
use App\Domain\Entity\MySurveyItem;
use App\Domain\Entity\User;
use App\Domain\Enum\ValidationErrorSlugEnum;
use App\Domain\Exception\ErrorException;
use App\Domain\Exception\ValidationException;
use App\Domain\Helper\HArray;
use App\Domain\Service\CompletedSurvey\CompletedSurveyService;
use App\Domain\Service\Db\TransactionManagerInterface;
use App\Domain\Service\SurveyItemAnswer\SurveyItemAnswerService;
use App\Domain\Validation\ValidationError;
use Psr\Log\LoggerInterface;
use Throwable;

//use App\Domain\Service\SurveyStat\StatRefresherInterface;

class CompleteSurveyService
{
    private LoggerInterface $logger;

    public function __construct(
        LoggerInterface $logger,
        private SurveyService $surveyService,
        private SurveyItemAnswerService $surveyItemAnswerService,
        private TransactionManagerInterface $transactionManager,
        private CompletedSurveyService $completedSurveyService,
//        private StatRefresherInterface $statRefresher,
    ) {
        $this->setLogger($logger);
    }

    public function setLogger(LoggerInterface $logger): CompleteSurveyService
    {
        $this->logger = $logger;
        $this->surveyService->setLogger($logger);
        $this->surveyItemAnswerService->setLogger($logger);
        $this->completedSurveyService->setLogger($logger);
        $this->statRefresher->setLogger($logger);
        return $this;
    }

    public function complete(User $user, CompleteSurveyDto $dto): void
    {
        $survey = $this->surveyService->getMyById(
            $user,
            new GetMySurveyByIdDto($dto->getId(), null, true),
        );
        if ($survey === null) {
            throw ErrorException::new('Опрос не найден', 404);
        }

        if ($survey->isCompleted()) {
            throw ErrorException::new('Опрос уже пройден', 400);
        }

        $answers = $dto->getAnswers();

        /** @var array<string, MySurveyItem> $items */
        $items = HArray::index(
            $survey->getMyItems(),
            fn(MySurveyItem $item): string => $item->getId()->toRfc4122(),
        );

        foreach ($answers as $k => $answer) {
            $item = $items[$answer->getId()->toRfc4122()] ?? null;
            if ($item === null) {
                throw ValidationException::new([
                    new ValidationError(
                        "[$k].id",
                        ValidationErrorSlugEnum::NotFound->getSlug(),
                        'Такого вопроса не существует',
                    ),
                ]);
            }
            if ($answer->getData() === null && $item->getSurveyItem()->isAnswerRequired()) {
                throw ValidationException::new([
                    new ValidationError(
                        "[$k].data",
                        ValidationErrorSlugEnum::WrongField->getSlug(),
                        'Ответ на этот вопрос обязателен',
                    ),
                ]);
            }
            if ($answer->getData() !== null && $answer->getData()->getType() !== $item->getSurveyItem()->getType()) {
                throw ValidationException::new([
                    new ValidationError(
                        "[$k].data.type",
                        ValidationErrorSlugEnum::WrongField->getSlug(),
                        'Неверный тип ответа',
                    ),
                ]);
            }
        }

        /** @var array<string, CompleteSurveyItemDto> $indexedAnswers */
        $indexedAnswers = HArray::index(
            $dto->getAnswers(),
            fn(CompleteSurveyItemDto $item): string => $item->getId()->toRfc4122(),
        );

        if (count($indexedAnswers) !== count($answers)) {
            throw ErrorException::new(
                'Некоторое вопросы отвечены несколько раз',
                400,
            );
        }

        $idToIndex = array_combine(array_keys($indexedAnswers), array_keys($answers));

        $createAnswerDtos = [];
        foreach ($items as $id => $item) {
            $answer = $indexedAnswers[$id] ?? null;
            $k = $idToIndex[$id] ?? null;

            if ($item->getSurveyItem()->isAnswerRequired()) {
                if ($answer === null) {
                    throw ErrorException::new(
                        'Пропущены ответы на некоторые вопросы',
                        400,
                    );
                }
                if ($answer->getData() === null) {
                    throw ValidationException::new([
                        new ValidationError(
                            "[$k].data",
                            ValidationErrorSlugEnum::WrongField->getSlug(),
                            'Ответ на этот вопрос обязателен',
                        ),
                    ]);
                }
            }

            if ($answer?->getData() !== null) {
                $createAnswerDto = new CreateSurveyItemAnswerDto(
                    $item->getSurveyItem(),
                    $item->getTeacherSubject(),
                    $answer->getData(),
                );
                try {
                    $this->surveyItemAnswerService->validateCreateDto($createAnswerDto);
                    $createAnswerDtos[] = $createAnswerDto;
                } catch (ValidationException $e) {
                    throw ValidationException::new(
                        array_map(
                            fn(ValidationError $er) => new ValidationError(
                                "[$k].data",
                                $er->getSlug(),
                                $er->getMessage(),
                            ),
                            $e->getErrors(),
                        ),
                    );
                } catch (Throwable $e) {
                    $this->logger->error($e);
                    throw ErrorException::new('Что-то пошло не так, обратитесь в поддержку');
                }
            }
        }

        try {
            $this->transactionManager->beginTransaction();
            $this->surveyItemAnswerService->createMulti($createAnswerDtos, false, false, true);

            $this
                ->completedSurveyService
                ->create(
                    new CreateCompletedSurveyDto(
                        $survey->getSurvey(),
                        $user,
                    ),
                );

            $this->transactionManager->commit();
        } catch (Throwable $e) {
            $this->transactionManager->rollback();
            $this->logger->error($e);
            throw ErrorException::new('Не удалось сохранить ответы, обратитесь в поддержку');
        }
    }
}
