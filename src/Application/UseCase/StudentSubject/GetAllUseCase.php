<?php

namespace App\Application\UseCase\StudentSubject;

use App\Application\Dto\StudentSubject\GetAllStudentSubjectsDto;
use App\Domain\DataProvider\DataProviderInterface;
use App\Domain\Dto\StudentSubject\GetAllStudentSubjectsDto as DomainGetAllStudentSubjectsDto;
use App\Domain\Entity\StudentSubject;
use App\Domain\Enum\SortTypeEnum;
use App\Domain\Service\StudentSubject\StudentSubjectService;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;

class GetAllUseCase
{
    private LoggerInterface $logger;

    public function __construct(
        LoggerInterface $logger,
        private StudentSubjectService $userSubjectService,
    ) {
        $this->setLogger($logger);
    }

    public function setLogger(LoggerInterface $logger): GetAllUseCase
    {
        $this->logger = $logger;
        $this->userSubjectService->setLogger($logger);
        return $this;
    }

    /**
     * @param GetAllStudentSubjectsDto $dto
     *
     * @return DataProviderInterface<StudentSubject>
     */
    public function execute(GetAllStudentSubjectsDto $dto): DataProviderInterface
    {
        return $this
            ->userSubjectService
            ->getAll(
                new DomainGetAllStudentSubjectsDto(
                    $dto->user_ids !== null
                        ? array_map(Uuid::fromRfc4122(...), $dto->user_ids)
                        : null,
                    $dto->teacher_ids !== null
                        ? array_map(Uuid::fromRfc4122(...), $dto->teacher_ids)
                        : null,
                    $dto->subject_ids !== null
                        ? array_map(Uuid::fromRfc4122(...), $dto->subject_ids)
                        : null,
                    $dto->is_actual_from !== null
                        ? new DateTimeImmutable($dto->is_actual_from)
                        : null,
                    $dto->is_actual_to !== null
                        ? new DateTimeImmutable($dto->is_actual_to)
                        : null,
                    $dto->sort_by,
                    SortTypeEnum::from($dto->sort_type),
                    $dto->offset,
                    $dto->limit,
                ),
            );
    }
}
