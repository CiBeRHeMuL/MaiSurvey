<?php

namespace App\Application\UseCase\StudentSubject;

use App\Application\Dto\StudentSubject\GetMyStudentSubjectsDto;
use App\Domain\DataProvider\DataProviderInterface;
use App\Domain\Dto\StudentSubject\GetMyStudentSubjectsDto as DomainGetMyStudentSubjectsDto;
use App\Domain\Entity\StudentSubject;
use App\Domain\Entity\User;
use App\Domain\Enum\SortTypeEnum;
use App\Domain\Service\StudentSubject\StudentSubjectService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;

class GetMyUseCase
{
    private LoggerInterface $logger;

    public function __construct(
        LoggerInterface $logger,
        private StudentSubjectService $userSubjectService,
    ) {
        $this->setLogger($logger);
    }

    public function setLogger(LoggerInterface $logger): GetMyUseCase
    {
        $this->logger = $logger;
        $this->userSubjectService->setLogger($logger);
        return $this;
    }

    /**
     * @param User $me
     * @param GetMyStudentSubjectsDto $dto
     *
     * @return DataProviderInterface<StudentSubject>
     */
    public function execute(User $me, GetMyStudentSubjectsDto $dto): DataProviderInterface
    {
        return $this
            ->userSubjectService
            ->getMy(
                $me,
                new DomainGetMyStudentSubjectsDto(
                    $dto->actual,
                    $dto->subject_ids !== null
                        ? array_map(Uuid::fromRfc4122(...), $dto->subject_ids)
                        : null,
                    $dto->teacher_ids !== null
                        ? array_map(Uuid::fromRfc4122(...), $dto->teacher_ids)
                        : null,
                    $dto->sort_by,
                    SortTypeEnum::from($dto->sort_type),
                    $dto->offset,
                    $dto->limit,
                ),
            );
    }
}
