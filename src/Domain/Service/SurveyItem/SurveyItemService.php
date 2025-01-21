<?php

namespace App\Domain\Service\SurveyItem;

use App\Domain\Dto\SurveyItem\Choice;
use App\Domain\Dto\SurveyItem\ChoiceItemData;
use App\Domain\Dto\SurveyItem\CommentItemData;
use App\Domain\Dto\SurveyItem\CreateSurveyItemDto;
use App\Domain\Dto\SurveyItem\MultiChoiceItemData;
use App\Domain\Dto\SurveyItem\RatingItemData;
use App\Domain\Entity\SurveyItem;
use App\Domain\Enum\SurveyItemTypeEnum;
use App\Domain\Enum\ValidationErrorSlugEnum;
use App\Domain\Exception\ErrorException;
use App\Domain\Exception\ValidationException;
use App\Domain\Repository\SurveyItemRepositoryInterface;
use App\Domain\Validation\ValidationError;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;

class SurveyItemService
{
    private LoggerInterface $logger;

    public function __construct(
        LoggerInterface $logger,
        private SurveyItemRepositoryInterface $surveyItemRepository,
    ) {
        $this->setLogger($logger);
    }

    public function setLogger(LoggerInterface $logger): SurveyItemService
    {
        $this->logger = $logger;
        return $this;
    }

    public function create(CreateSurveyItemDto $dto): SurveyItem
    {
        $this->validateCreateDto($dto);
        $entity = $this->entityFromCreateDto($dto);
        $created = $this
            ->surveyItemRepository
            ->create($entity);
        if ($created === false) {
            throw ErrorException::new(
                'Не удалось создать вопрос, обратитесь в поддержку',
            );
        }
        return $entity;
    }

    public function validateCreateDto(CreateSurveyItemDto $dto): void
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

        $data = $dto->getData();
        switch ($data->getType()) {
            case SurveyItemTypeEnum::Rating:
                /** @var RatingItemData $data */
                if ($data->getMin() >= $data->getMax()) {
                    throw ValidationException::new([
                        new ValidationError(
                            'data.min',
                            ValidationErrorSlugEnum::WrongField->getSlug(),
                            'Минимальное значение оценки должно быть меньше максимального',
                        ),
                    ]);
                }
                break;
            case SurveyItemTypeEnum::Comment:
                /** @var CommentItemData $data */
                if (
                    $data->getPlaceholder() !== null
                    && mb_strlen($data->getPlaceholder()) > 255
                ) {
                    throw ValidationException::new([
                        new ValidationError(
                            'data.placeholder',
                            ValidationErrorSlugEnum::WrongField->getSlug(),
                            'Плейсхолдер должен быть короче 255 символов',
                        ),
                    ]);
                }
                if (
                    $data->getMaxLength() <= 0
                    || $data->getMaxLength() > 1000
                ) {
                    throw ValidationException::new([
                        new ValidationError(
                            'data.placeholder',
                            ValidationErrorSlugEnum::WrongField->getSlug(),
                            'Максимальная длина должна быть больше 0 и меньше либо равна 1000',
                        ),
                    ]);
                }
                break;
            case SurveyItemTypeEnum::Choice:
            case SurveyItemTypeEnum::MultiChoice:
                /**
                 * @var MultiChoiceItemData|ChoiceItemData $data
                 * @var int $k
                 * @var Choice $choice
                 */
                foreach ($data->getChoices() as $k => $choice) {
                    if (mb_strlen($choice->text) > 255) {
                        throw ValidationException::new([
                            new ValidationError(
                                "data.choices[$k].text",
                                ValidationErrorSlugEnum::WrongField->getSlug(),
                                'Описание выбора должно быть короче 255 символов',
                            ),
                        ]);
                    }
                    if (mb_strlen($choice->value) > 255) {
                        throw ValidationException::new([
                            new ValidationError(
                                "data.choices[$k].value",
                                ValidationErrorSlugEnum::WrongField->getSlug(),
                                'Значение выбора должно быть короче 255 символов',
                            ),
                        ]);
                    }
                }
                break;
        }
    }

    public function entityFromCreateDto(CreateSurveyItemDto $dto): SurveyItem
    {
        $entity = new SurveyItem();
        $entity
            ->setSurveyId($dto->getSurvey()->getId())
            ->setAnswerRequired($dto->isAnswerRequired())
            ->setType($dto->getType())
            ->setText(trim($dto->getText()))
            ->setPosition($dto->getPosition())
            ->setData($dto->getData())
            ->setSubjectType($dto->getSubjectType())
            ->setCreatedAt(new DateTimeImmutable())
            ->setSurvey($dto->getSurvey());
        return $entity;
    }
}
