<?php

namespace App\Domain\Service\Survey;

use App\Domain\DataProvider\DataProviderInterface;
use App\Domain\DataProvider\ProjectionAwareDataProvider;
use App\Domain\Dto\Survey\CreateSurveyDto;
use App\Domain\Dto\Survey\GetMySurveyByIdDto;
use App\Domain\Dto\Survey\GetMySurveysDto;
use App\Domain\Dto\SurveyItem\CreateSurveyItemDto;
use App\Domain\Entity\MySurvey;
use App\Domain\Entity\Survey;
use App\Domain\Entity\User;
use App\Domain\Enum\ValidationErrorSlugEnum;
use App\Domain\Exception\ErrorException;
use App\Domain\Exception\ValidationException;
use App\Domain\Repository\SurveyRepositoryInterface;
use App\Domain\Service\Db\TransactionManagerInterface;
use App\Domain\Service\Subject\SubjectService;
use App\Domain\Service\SurveyItem\SurveyItemService;
use App\Domain\Service\Template\TemplateService;
use App\Domain\Validation\ValidationError;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;
use Throwable;

class SurveyService
{
    public const array GET_MY_SORT = ['name', 'completed', 'created_at'];

    private LoggerInterface $logger;

    public function __construct(
        private SurveyRepositoryInterface $surveyRepository,
        private TemplateService $templateService,
        private SubjectService $subjectService,
        private TransactionManagerInterface $transactionManager,
        private SurveyItemService $surveyItemService,
        LoggerInterface $logger,
    ) {
        $this->setLogger($logger);
    }

    public function setLogger(LoggerInterface $logger): SurveyService
    {
        $this->logger = $logger;
        $this->subjectService->setLogger($logger);
        $this->surveyItemService->setLogger($logger);
        return $this;
    }

    /**
     * @param User $user
     * @param GetMySurveysDto $dto
     *
     * @return DataProviderInterface<MySurvey>
     */
    public function getMy(User $user, GetMySurveysDto $dto): DataProviderInterface
    {
        if (!in_array($dto->getSortBy(), self::GET_MY_SORT, true)) {
            throw ValidationException::new([
                new ValidationError(
                    'name',
                    ValidationErrorSlugEnum::WrongField->getSlug(),
                    sprintf('Сортировка доступна по полям: %s', implode(', ', self::GET_MY_SORT)),
                ),
            ]);
        }

        return new ProjectionAwareDataProvider(
            $this
                ->surveyRepository
                ->findMy($user, $dto),
            $this->prepareMySurvey(...),
        );
    }

    public function getMyById(User $user, GetMySurveyByIdDto $dto): MySurvey|null
    {
        $survey = $this
            ->surveyRepository
            ->findMyById($user, $dto);
        return $survey !== null ? $this->prepareMySurvey($survey) : null;
    }

    public function create(CreateSurveyDto $dto): Survey
    {
        $this->validateCreateDto($dto);
        try {
            $this->transactionManager->beginTransaction();
            $entity = $this->entityFromCreateDto($dto);
            $created = $this
                ->surveyRepository
                ->create($entity);
            if ($created === false) {
                throw ErrorException::new(
                    'Не удалось создать опрос, обратитесь в поддержку',
                );
            }

            foreach ($dto->getItems() as $k => $itemDto) {
                $createItemDto = new CreateSurveyItemDto(
                    $entity,
                    $itemDto->isAnswerRequired(),
                    $itemDto->getType(),
                    $itemDto->getText(),
                    $itemDto->getPosition(),
                    $itemDto->getData(),
                    $itemDto->getSubjectType(),
                );

                try {
                    $surveyItem = $this
                        ->surveyItemService
                        ->create($createItemDto);
                    $entity->addItem($surveyItem);
                } catch (ValidationException $e) {
                    throw ValidationException::new(
                        array_map(
                            fn(ValidationError $er) => new ValidationError(
                                "items[$k].{$er->getField()}",
                                $er->getSlug(),
                                $er->getMessage(),
                            ),
                            $e->getErrors(),
                        ),
                    );
                }
            }

            $this->transactionManager->commit();
            return $entity;
        } catch (Throwable $e) {
            $this->logger->error($e);
            $this->transactionManager->rollback();
            throw ErrorException::new(
                'Не удалось сохранить опрос, обратитесь в поддержку',
            );
        }
    }

    public function entityFromCreateDto(CreateSurveyDto $dto): Survey
    {
        $entity = new Survey();
        $entity
            ->setTitle(trim($dto->getTitle()))
            ->setSubjectId($dto->getSubjectId())
            ->setActualTo($dto->getActualTo())
            ->setCreatedAt(new DateTimeImmutable())
            ->setSubject(
                $this
                    ->subjectService
                    ->getById($dto->getSubjectId()),
            );
        return $entity;
    }

    public function validateCreateDto(CreateSurveyDto $dto): void
    {
        if (mb_strlen($dto->getTitle()) > 255) {
            throw ValidationException::new([
                new ValidationError(
                    'title',
                    ValidationErrorSlugEnum::WrongField->getSlug(),
                    'Название должно быть короче 255 символов',
                ),
            ]);
        }
        $subject = $this
            ->subjectService
            ->getById($dto->getSubjectId());
        if ($subject === null) {
            throw ValidationException::new([
                new ValidationError(
                    'subject_id',
                    ValidationErrorSlugEnum::NotFound->getSlug(),
                    'Предмет не найден',
                ),
            ]);
        }

        if ($dto->getActualTo()->getTimestamp() <= (new DateTimeImmutable())->getTimestamp()) {
            throw ValidationException::new([
                new ValidationError(
                    'actual_to',
                    ValidationErrorSlugEnum::NotFound->getSlug(),
                    'Время окончания опроса должно быть больше текущего',
                ),
            ]);
        }
    }

    private function prepareMySurvey(MySurvey $survey): MySurvey
    {
        $items = $survey->getMyItems()->toArray();
        array_walk($items, $this->templateService->putTsIntoMySurveyItem(...));
        return $survey;
    }
}
