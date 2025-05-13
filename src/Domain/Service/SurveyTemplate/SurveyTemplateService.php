<?php

namespace App\Domain\Service\SurveyTemplate;

use App\Domain\DataProvider\DataProviderInterface;
use App\Domain\Dto\SurveyTemplate\CreateSurveyTemplateDto;
use App\Domain\Dto\SurveyTemplate\GetAllSurveyTemplatesDto;
use App\Domain\Dto\SurveyTemplateItem\CreateSurveyTemplateItemDto;
use App\Domain\Entity\SurveyTemplate;
use App\Domain\Enum\ValidationErrorSlugEnum;
use App\Domain\Exception\ErrorException;
use App\Domain\Exception\ValidationException;
use App\Domain\Repository\SurveyTemplateRepositoryInterface;
use App\Domain\Service\Db\TransactionManagerInterface;
use App\Domain\Service\SurveyTemplateItem\SurveyTemplateItemService;
use App\Domain\Validation\ValidationError;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;
use Throwable;

class SurveyTemplateService
{
    public const array GET_ALL_SORT = ['name', 'created_at'];

    private LoggerInterface $logger;

    public function __construct(
        private SurveyTemplateRepositoryInterface $surveyTemplateRepository,
        private TransactionManagerInterface $transactionManager,
        private SurveyTemplateItemService $surveyTemplateItemService,
        LoggerInterface $logger,
    ) {
        $this->setLogger($logger);
    }

    public function setLogger(LoggerInterface $logger): SurveyTemplateService
    {
        $this->logger = $logger;
        $this->surveyTemplateItemService->setLogger($logger);
        return $this;
    }

    /**
     * @param GetAllSurveyTemplatesDto $dto
     *
     * @return DataProviderInterface<SurveyTemplate>
     */
    public function getAll(GetAllSurveyTemplatesDto $dto): DataProviderInterface
    {
        if ($dto->getName() !== null && mb_strlen($dto->getName()) < 3) {
            throw ValidationException::new([
                new ValidationError(
                    'name',
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
                    sprintf('Сортировка доступна по полям: %s', implode(', ', self::GET_ALL_SORT)),
                ),
            ]);
        }

        return $this
            ->surveyTemplateRepository
            ->findAll($dto);
    }

    public function getById(Uuid $id): SurveyTemplate|null
    {
        return $this
            ->surveyTemplateRepository
            ->findById($id);
    }

    public function create(CreateSurveyTemplateDto $dto): SurveyTemplate
    {
        $this->validateCreateDto($dto);
        try {
            $this->transactionManager->beginTransaction();
            $entity = $this->entityFromCreateDto($dto);
            $created = $this
                ->surveyTemplateRepository
                ->create($entity);
            if ($created === false) {
                throw ErrorException::new(
                    'Не удалось создать шаблон опроса, обратитесь в поддержку',
                );
            }

            foreach ($dto->getItems() as $k => $itemDto) {
                $createItemDto = new CreateSurveyTemplateItemDto(
                    $entity,
                    $itemDto->isAnswerRequired(),
                    $itemDto->getType(),
                    $itemDto->getText(),
                    $itemDto->getPosition(),
                    $itemDto->getData(),
                    $itemDto->getSubjectType(),
                );

                try {
                    $surveyTemplateItem = $this
                        ->surveyTemplateItemService
                        ->create($createItemDto);
                    $entity->addItem($surveyTemplateItem);
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
            if ($e instanceof ValidationException || $e instanceof ErrorException) {
                $this->transactionManager->rollback();
                throw $e;
            }
            $this->logger->error('An error occurred', ['exception' => $e]);
            $this->transactionManager->rollback();
            throw ErrorException::new(
                'Не удалось сохранить шаблон опроса, обратитесь в поддержку',
            );
        }
    }

    public function entityFromCreateDto(CreateSurveyTemplateDto $dto): SurveyTemplate
    {
        $entity = new SurveyTemplate();
        $entity
            ->setTitle(trim($dto->getTitle()))
            ->setName(trim($dto->getName()))
            ->setCreatedAt(new DateTimeImmutable());
        return $entity;
    }

    public function validateCreateDto(CreateSurveyTemplateDto $dto): void
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
        if (mb_strlen($dto->getName()) > 255) {
            throw ValidationException::new([
                new ValidationError(
                    'name',
                    ValidationErrorSlugEnum::WrongField->getSlug(),
                    'Название должно быть короче 255 символов',
                ),
            ]);
        }
    }
}
