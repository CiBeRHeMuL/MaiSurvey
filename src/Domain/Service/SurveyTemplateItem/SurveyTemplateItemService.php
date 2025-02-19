<?php

namespace App\Domain\Service\SurveyTemplateItem;

use App\Domain\Dto\SurveyTemplateItem\CreateSurveyTemplateItemDto;
use App\Domain\Entity\SurveyTemplateItem;
use App\Domain\Enum\ValidationErrorSlugEnum;
use App\Domain\Exception\ErrorException;
use App\Domain\Exception\ValidationException;
use App\Domain\Repository\SurveyTemplateItemRepositoryInterface;
use App\Domain\Service\SurveyItem\SurveyItemService;
use App\Domain\Validation\ValidationError;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;

class SurveyTemplateItemService
{
    private LoggerInterface $logger;

    public function __construct(
        LoggerInterface $logger,
        private SurveyTemplateItemRepositoryInterface $surveyTemplateItemRepository,
        private SurveyItemService $surveyItemService,
    ) {
        $this->setLogger($logger);
    }

    public function setLogger(LoggerInterface $logger): SurveyTemplateItemService
    {
        $this->logger = $logger;
        $this->surveyItemService->setLogger($logger);
        return $this;
    }

    public function create(CreateSurveyTemplateItemDto $dto): SurveyTemplateItem
    {
        $this->validateCreateDto($dto);
        $entity = $this->entityFromCreateDto($dto);
        $created = $this
            ->surveyTemplateItemRepository
            ->create($entity);
        if ($created === false) {
            throw ErrorException::new(
                'Не удалось создать шаблон вопроса, обратитесь в поддержку',
            );
        }
        return $entity;
    }

    public function validateCreateDto(CreateSurveyTemplateItemDto $dto): void
    {
        if ($dto->getType() !== $dto->getData()->getType()) {
            throw ValidationException::new([
                new ValidationError(
                    'type',
                    ValidationErrorSlugEnum::WrongField->getSlug(),
                    'Тип вопроса не совпадает с типом данных',
                ),
            ]);
        }

        $this->surveyItemService->validateData($dto->getData());
    }

    public function entityFromCreateDto(CreateSurveyTemplateItemDto $dto): SurveyTemplateItem
    {
        $entity = new SurveyTemplateItem();
        $entity
            ->setSurveyTemplateId($dto->getSurveyTemplate()->getId())
            ->setAnswerRequired($dto->isAnswerRequired())
            ->setType($dto->getType())
            ->setText(trim($dto->getText()))
            ->setPosition($dto->getPosition())
            ->setData($dto->getData())
            ->setSubjectType($dto->getSubjectType())
            ->setCreatedAt(new DateTimeImmutable())
            ->setSurveyTemplate($dto->getSurveyTemplate());
        return $entity;
    }
}
