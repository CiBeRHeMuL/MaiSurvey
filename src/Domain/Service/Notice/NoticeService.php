<?php

namespace App\Domain\Service\Notice;

use App\Domain\Dto\Notice\CreateNoticeDto;
use App\Domain\Dto\Notice\NewSurveyNoticeContext;
use App\Domain\Entity\Notice;
use App\Domain\Enum\NoticeStatusEnum;
use App\Domain\Enum\NoticeTypeEnum;
use App\Domain\Enum\ValidationErrorSlugEnum;
use App\Domain\Exception\ErrorException;
use App\Domain\Exception\ValidationException;
use App\Domain\Repository\NoticeRepositoryInterface;
use App\Domain\Service\Survey\SurveyService;
use App\Domain\Validation\ValidationError;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;

class NoticeService
{
    public function __construct(
        private LoggerInterface $logger,
        private NoticeRecipientIdResolverInterface $noticeRecipientIdResolver,
        private NoticeRepositoryInterface $noticeRepository,
        private NoticeRendererFactoryInterface $noticeRendererFactory,
        private SurveyService $surveyService,
    ) {
        $this->setLogger($this->logger);
    }

    public function setLogger(LoggerInterface $logger): NoticeService
    {
        $this->logger = $logger;
        $this->surveyService->setLogger($logger);
        return $this;
    }

    public function create(CreateNoticeDto $dto): Notice
    {
        $this->validateCreateDto($dto);

        $entity = $this->entityFromCreateDto($dto);

        $created = $this->noticeRepository->create($entity);
        if (!$created) {
            throw ErrorException::new('Не удалось создать уведомление');
        }
        return $entity;
    }

    public function validateCreateDto(CreateNoticeDto $dto, bool $checkExistingInContext = true): void
    {
        if ($dto->getUser()->isActive() === false) {
            throw ValidationException::new([
                new ValidationError(
                    'user',
                    ValidationErrorSlugEnum::WrongField->getSlug(),
                    'Пользователь должен быть активным',
                ),
            ]);
        }

        if (!in_array($dto->getType(), $dto->getUser()->getNoticeTypes(), true)) {
            throw ValidationException::new([
                new ValidationError(
                    'type',
                    ValidationErrorSlugEnum::WrongField->getSlug(),
                    'Такой тип уведомлений не подключен',
                ),
            ]);
        }
        if (!in_array($dto->getChannel(), $dto->getUser()->getNoticeChannels(), true)) {
            throw ValidationException::new([
                new ValidationError(
                    'type',
                    ValidationErrorSlugEnum::WrongField->getSlug(),
                    'Такой способ уведомлений не подключен',
                ),
            ]);
        }

        $context = $dto->getContext();

        switch ($context->getType()) {
            case NoticeTypeEnum::NewSurvey:
                /** @var NewSurveyNoticeContext $context */
                if ($checkExistingInContext) {
                    $survey = $this->surveyService->getById($context->getSurveyId());
                    if ($survey === null) {
                        throw ValidationException::new([
                            new ValidationError(
                                'context.survey_id',
                                ValidationErrorSlugEnum::NotFound->getSlug(),
                                'Опрос не найден',
                            ),
                        ]);
                    } elseif ($survey->isActual() === false) {
                        throw ValidationException::new([
                            new ValidationError(
                                'context.survey_id',
                                ValidationErrorSlugEnum::NotFound->getSlug(),
                                'Опрос должен быть актуальным',
                            ),
                        ]);
                    }
                }
        }
    }

    public function entityFromCreateDto(CreateNoticeDto $dto): Notice
    {
        $recipientId = $this->noticeRecipientIdResolver->getIdentifier($dto->getUser());

        $entity = new Notice();
        $entity->setType($dto->getType())
            ->setChannel($dto->getChannel())
            ->setUserId($dto->getUser()->getId())
            ->setUser($dto->getUser())
            ->setStatus(NoticeStatusEnum::Created)
            ->setContext($dto->getContext())
            ->setText('')
            ->setRecipientId($recipientId)
            ->setCreatedAt(new DateTimeImmutable())
            ->setUpdatedAt(new DateTimeImmutable());

        $renderer = $this->noticeRendererFactory->getRenderer($entity);

        $text = $renderer->render($entity);

        $entity->setText($text);

        return $entity;
    }
}
