<?php

namespace App\Domain\Service\CompletedSurvey;

use App\Domain\Dto\CompletedSurvey\CreateCompletedSurveyDto;
use App\Domain\Dto\CompletedSurvey\GetCompletedSurveyByIndexDto;
use App\Domain\Entity\CompletedSurvey;
use App\Domain\Enum\ValidationErrorSlugEnum;
use App\Domain\Exception\ErrorException;
use App\Domain\Exception\ValidationException;
use App\Domain\Repository\CompletedSurveyRepositoryInterface;
use App\Domain\Validation\ValidationError;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;

class CompletedSurveyService
{
    private LoggerInterface $logger;

    public function __construct(
        LoggerInterface $logger,
        private CompletedSurveyRepositoryInterface $completedSurveyRepository,
    ) {
        $this->setLogger($logger);
    }

    public function setLogger(LoggerInterface $logger): CompletedSurveyService
    {
        $this->logger = $logger;
        return $this;
    }

    public function create(CreateCompletedSurveyDto $dto, bool $validate = true): CompletedSurvey
    {
        if ($validate) {
            $this->validateCreateDto($dto);
        }
        $entity = $this->entityFromCreateDto($dto);
        if ($this->completedSurveyRepository->create($entity) === false) {
            throw ErrorException::new(
                'Не удалось завершить опрос',
                400,
            );
        }
        return $entity;
    }

    public function entityFromCreateDto(CreateCompletedSurveyDto $dto): CompletedSurvey
    {
        $entity = new CompletedSurvey();
        $entity
            ->setSurveyId($dto->getSurvey()->getId())
            ->setUserId($dto->getUser()->getId())
            ->setCreatedAt(new DateTimeImmutable())
            ->setSurvey($dto->getSurvey())
            ->setUser($dto->getUser());
        return $entity;
    }

    public function validateCreateDto(CreateCompletedSurveyDto $dto, bool $checkExisting = true): void
    {
        if ($checkExisting) {
            $existing = $this
                ->completedSurveyRepository
                ->findByIndex(
                    new GetCompletedSurveyByIndexDto(
                        $dto->getSurvey()->getId(),
                        $dto->getUser()->getId(),
                    ),
                );
            if ($existing !== null) {
                throw ValidationException::new([
                    new ValidationError(
                        'user_id',
                        ValidationErrorSlugEnum::AlreadyExists->getSlug(),
                        'Опрос уже завершен',
                    ),
                    new ValidationError(
                        'survey_id',
                        ValidationErrorSlugEnum::AlreadyExists->getSlug(),
                        'Опрос уже завершен',
                    ),
                ]);
            }
        }
    }
}
