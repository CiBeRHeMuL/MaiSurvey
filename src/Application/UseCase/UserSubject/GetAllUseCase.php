<?php

namespace App\Application\UseCase\UserSubject;

use App\Application\Dto\UserSubject\GetAllDto;
use App\Domain\DataProvider\DataProviderInterface;
use App\Domain\Dto\UserSubject\GetAllUserSubjectsDto;
use App\Domain\Entity\UserSubject;
use App\Domain\Enum\SortTypeEnum;
use App\Domain\Service\UserSubject\UserSubjectService;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;

class GetAllUseCase
{
    private LoggerInterface $logger;

    public function __construct(
        LoggerInterface $logger,
        private UserSubjectService $userSubjectService,
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
     * @param GetAllDto $dto
     *
     * @return DataProviderInterface<UserSubject>
     */
    public function execute(GetAllDto $dto): DataProviderInterface
    {
        return $this
            ->userSubjectService
            ->getAll(
                new GetAllUserSubjectsDto(
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
