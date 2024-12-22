<?php

namespace App\Application\UseCase\TeacherSubject;

use App\Application\Dto\TeacherSubject\GetAllTeacherSubjectsDto;
use App\Domain\DataProvider\DataProviderInterface;
use App\Domain\Dto\TeacherSubject\GetAllTeacherSubjectsDto as DomainGetAllTeacherSubjectsDto;
use App\Domain\Entity\TeacherSubject;
use App\Domain\Enum\SortTypeEnum;
use App\Domain\Service\TeacherSubject\TeacherSubjectService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;

class GetAllUseCase
{
    private LoggerInterface $logger;

    public function __construct(
        LoggerInterface $logger,
        private TeacherSubjectService $teacherSubjectService,
    ) {
        $this->setLogger($logger);
    }

    public function setLogger(LoggerInterface $logger): GetAllUseCase
    {
        $this->logger = $logger;
        $this->teacherSubjectService->setLogger($logger);
        return $this;
    }

    /**
     * @param GetAllTeacherSubjectsDto $dto
     *
     * @return DataProviderInterface<TeacherSubject>
     */
    public function execute(GetAllTeacherSubjectsDto $dto): DataProviderInterface
    {
        return $this
            ->teacherSubjectService
            ->getAll(
                new DomainGetAllTeacherSubjectsDto(
                    $dto->teacher_ids !== null
                        ? array_map(Uuid::fromRfc4122(...), $dto->teacher_ids)
                        : null,
                    $dto->subject_ids !== null
                        ? array_map(Uuid::fromRfc4122(...), $dto->subject_ids)
                        : null,
                    $dto->sort_by,
                    SortTypeEnum::from($dto->sort_type),
                    $dto->offset,
                    $dto->limit,
                ),
            );
    }
}
