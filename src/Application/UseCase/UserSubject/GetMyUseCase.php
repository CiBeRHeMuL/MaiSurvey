<?php

namespace App\Application\UseCase\UserSubject;

use App\Application\Dto\UserSubject\GetMyDto;
use App\Domain\DataProvider\DataProviderInterface;
use App\Domain\Dto\UserSubject\GetMyUserSubjectsDto;
use App\Domain\Entity\User;
use App\Domain\Entity\UserSubject;
use App\Domain\Enum\SortTypeEnum;
use App\Domain\Service\UserSubject\UserSubjectService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;

class GetMyUseCase
{
    private LoggerInterface $logger;

    public function __construct(
        LoggerInterface $logger,
        private UserSubjectService $userSubjectService,
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
     * @param GetMyDto $dto
     *
     * @return DataProviderInterface<UserSubject>
     */
    public function execute(User $me, GetMyDto $dto): DataProviderInterface
    {
        return $this
            ->userSubjectService
            ->getMy(
                $me,
                new GetMyUserSubjectsDto(
                    $dto->actual,
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
