<?php

namespace App\Domain\Service\UserSubject;

use App\Domain\DataProvider\DataProviderInterface;
use App\Domain\Dto\UserSubject\GetAllUserSubjectsDto;
use App\Domain\Dto\UserSubject\GetMyUserSubjectsDto;
use App\Domain\Entity\User;
use App\Domain\Entity\UserSubject;
use App\Domain\Enum\ValidationErrorSlugEnum;
use App\Domain\Exception\ValidationException;
use App\Domain\Repository\UserSubjectRepositoryInterface;
use App\Domain\Validation\ValidationError;
use Psr\Log\LoggerInterface;

class UserSubjectService
{
    public const array GET_ALL_SORT = ['name', 'actual_from', 'actual_to'];

    private LoggerInterface $logger;

    public function __construct(
        LoggerInterface $logger,
        private UserSubjectRepositoryInterface $userSubjectRepository,
    ) {
        $this->setLogger($logger);
    }

    public function setLogger(LoggerInterface $logger): UserSubjectService
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * @param GetAllUserSubjectsDto $dto
     *
     * @return DataProviderInterface<UserSubject>
     */
    public function getAll(GetAllUserSubjectsDto $dto): DataProviderInterface
    {
        if (!in_array($dto->getSortBy(), self::GET_ALL_SORT, true)) {
            throw ValidationException::new([
                new ValidationError(
                    'name',
                    ValidationErrorSlugEnum::WrongField->getSlug(),
                    sprintf('Сортировка доступна по полям: %s', implode(', ', self::GET_ALL_SORT)),
                ),
            ]);
        }

        return $this
            ->userSubjectRepository
            ->findAll($dto);
    }

    /**
     * @param User $user
     * @param GetMyUserSubjectsDto $dto
     *
     * @return DataProviderInterface<UserSubject>
     */
    public function getMy(User $user, GetMyUserSubjectsDto $dto): DataProviderInterface
    {
        if (!in_array($dto->getSortBy(), self::GET_ALL_SORT, true)) {
            throw ValidationException::new([
                new ValidationError(
                    'name',
                    ValidationErrorSlugEnum::WrongField->getSlug(),
                    sprintf('Сортировка доступна по полям: %s', implode(', ', self::GET_ALL_SORT)),
                ),
            ]);
        }

        return $this
            ->userSubjectRepository
            ->findMy($user, $dto);
    }
}
