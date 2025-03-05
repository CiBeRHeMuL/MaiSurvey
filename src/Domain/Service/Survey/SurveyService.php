<?php

namespace App\Domain\Service\Survey;

use App\Domain\DataProvider\DataProviderInterface;
use App\Domain\DataProvider\ProjectionAwareDataProvider;
use App\Domain\Dto\Survey\CreateItemDto;
use App\Domain\Dto\Survey\CreateSurveyDto;
use App\Domain\Dto\Survey\CreateSurveyFromTemplateDto;
use App\Domain\Dto\Survey\GetMySurveyByIdDto;
use App\Domain\Dto\Survey\GetMySurveysDto;
use App\Domain\Dto\Survey\GetSurveysDto;
use App\Domain\Dto\SurveyItem\CreateSurveyItemDto;
use App\Domain\Entity\MySurvey;
use App\Domain\Entity\Survey;
use App\Domain\Entity\SurveyTemplateItem;
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
use Symfony\Component\Uid\Uuid;
use Throwable;

class SurveyService
{
    public const array GET_MY_SORT = ['name', 'completed', 'created_at'];
    public const array GET_ALL_SORT = ['name', 'title', 'created_at'];

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
                    'sort_by',
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
        } catch (ErrorException|ValidationException $e) {
            $this->transactionManager->rollback();
            throw $e;
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
            ->setSubjectId($dto->getSubject()->getId())
            ->setActualTo($dto->getActualTo())
            ->setCreatedAt(new DateTimeImmutable())
            ->setSubject($dto->getSubject());
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

        if ($dto->getActualTo()->getTimestamp() <= $dto->getSubject()->getSemester()->getDateEnd()->getTimestamp()) {
            throw ValidationException::new([
                new ValidationError(
                    'actual_to',
                    ValidationErrorSlugEnum::WrongField->getSlug(),
                    'Дата окончания актуальности опроса должна быть больше даты окончания семестра',
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

    public function getById(Uuid $id): Survey|null
    {
        return $this
            ->surveyRepository
            ->findById($id);
    }

    /**
     * @param GetSurveysDto $dto
     *
     * @return DataProviderInterface<Survey>
     */
    public function getAll(GetSurveysDto $dto): DataProviderInterface
    {
        if ($dto->getTitle() !== null && mb_strlen($dto->getTitle()) < 3) {
            throw ValidationException::new([
                new ValidationError(
                    'title',
                    ValidationErrorSlugEnum::WrongField->getSlug(),
                    'Для поиска по названию название должно быть длиннее 3 символов',
                ),
            ]);
        }
        if (!in_array($dto->getSortBy(), self::GET_ALL_SORT, true)) {
            throw ValidationException::new([
                new ValidationError(
                    'sort_by',
                    ValidationErrorSlugEnum::WrongField->getSlug(),
                    sprintf('Сортировка доступна по полям: %s', implode(', ', self::GET_MY_SORT)),
                ),
            ]);
        }

        return $this
            ->surveyRepository
            ->findAll($dto);
    }

    /**
     * @param Uuid[] $ids
     * @param bool|null $actual
     *
     * @return Survey[]
     */
    public function getByIds(array $ids, bool|null $actual = null): array
    {
        return $this
            ->surveyRepository
            ->findByIds($ids, $actual);
    }

    private function prepareMySurvey(MySurvey $survey): MySurvey
    {
        $items = $survey->getMyItems()->toArray();
        array_walk($items, $this->templateService->putTsIntoMySurveyItem(...));
        return $survey;
    }

    public function createFromTemplate(CreateSurveyFromTemplateDto $dto): Survey
    {
        $template = $dto->getTemplate();
        try {
            return $this->create(
                new CreateSurveyDto(
                    $template->getTitle(),
                    $dto->getActualTo(),
                    array_map(
                        fn(SurveyTemplateItem $item) => new CreateItemDto(
                            $item->isAnswerRequired(),
                            $item->getType(),
                            $item->getText(),
                            $item->getPosition(),
                            $item->getData(),
                            $item->getSubjectType(),
                        ),
                        $template->getItems()->toArray(),
                    ),
                    $dto->getSubject(),
                ),
            );
        } catch (Throwable $e) {
            $this->logger->error($e);
            throw ErrorException::new('Не удалось создать опрос из шаблона, обратитесь в поддержку');
        }
    }
}
