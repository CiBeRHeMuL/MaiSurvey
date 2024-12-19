<?php

namespace App\Domain\Service\StudentSubject;

use App\Domain\DataProvider\DataProviderInterface;
use App\Domain\Dto\StudentSubject\GetAllStudentSubjectsDto;
use App\Domain\Dto\StudentSubject\GetMyStudentSubjectsDto;
use App\Domain\Entity\StudentSubject;
use App\Domain\Entity\User;
use App\Domain\Enum\ValidationErrorSlugEnum;
use App\Domain\Exception\ValidationException;
use App\Domain\Repository\StudentSubjectRepositoryInterface;
use App\Domain\Validation\ValidationError;
use Psr\Log\LoggerInterface;

class StudentSubjectService
{
    public const array GET_ALL_SORT = ['name', 'actual_from', 'actual_to'];

    private LoggerInterface $logger;

    public function __construct(
        LoggerInterface $logger,
        private StudentSubjectRepositoryInterface $userSubjectRepository,
    ) {
        $this->setLogger($logger);
    }

    public function setLogger(LoggerInterface $logger): StudentSubjectService
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * @param GetAllStudentSubjectsDto $dto
     *
     * @return DataProviderInterface<StudentSubject>
     */
    public function getAll(GetAllStudentSubjectsDto $dto): DataProviderInterface
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
     * @param GetMyStudentSubjectsDto $dto
     *
     * @return DataProviderInterface<StudentSubject>
     */
    public function getMy(User $user, GetMyStudentSubjectsDto $dto): DataProviderInterface
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
