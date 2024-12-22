<?php

namespace App\Application\UseCase\TeacherSubject;

use App\Application\Dto\TeacherSubject\GetMyTeacherSubjectsDto;
use App\Domain\DataProvider\DataProviderInterface;
use App\Domain\Dto\TeacherSubject\GetMyTeacherSubjectsDto as DomainGetMyTeacherSubjectsDto;
use App\Domain\Entity\MyTeacherSubject;
use App\Domain\Entity\User;
use App\Domain\Enum\SortTypeEnum;
use App\Domain\Service\TeacherSubject\TeacherSubjectService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;

class GetMyUseCase
{
    private LoggerInterface $logger;

    public function __construct(
        LoggerInterface $logger,
        private TeacherSubjectService $teacherSubjectService,
    ) {
        $this->setLogger($logger);
    }

    public function setLogger(LoggerInterface $logger): GetMyUseCase
    {
        $this->logger = $logger;
        $this->teacherSubjectService->setLogger($logger);
        return $this;
    }

    /**
     * @param User $me
     * @param GetMyTeacherSubjectsDto $dto
     *
     * @return DataProviderInterface<MyTeacherSubject>
     */
    public function execute(User $me, GetMyTeacherSubjectsDto $dto): DataProviderInterface
    {
        return $this
            ->teacherSubjectService
            ->getMy(
                $me,
                new DomainGetMyTeacherSubjectsDto(
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
