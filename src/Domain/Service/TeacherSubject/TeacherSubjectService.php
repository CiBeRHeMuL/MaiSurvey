<?php

namespace App\Domain\Service\TeacherSubject;

use App\Domain\DataProvider\DataProviderInterface;
use App\Domain\DataProvider\EmptyDataProvider;
use App\Domain\Dto\TeacherSubject\GetAllTeacherSubjectsDto;
use App\Domain\Dto\TeacherSubject\GetMyTeacherSubjectsDto;
use App\Domain\Entity\MyTeacherSubject;
use App\Domain\Entity\TeacherSubject;
use App\Domain\Entity\User;
use App\Domain\Enum\ValidationErrorSlugEnum;
use App\Domain\Exception\ValidationException;
use App\Domain\Repository\TeacherSubjectRepositoryInterface;
use App\Domain\Validation\ValidationError;
use Psr\Log\LoggerInterface;

class TeacherSubjectService
{
    public const array GET_ALL_SORT = ['name', 'created_at'];

    private LoggerInterface $logger;

    public function __construct(
        private TeacherSubjectRepositoryInterface $teacherSubjectRepository,
        LoggerInterface $logger,
    ) {
        $this->setLogger($logger);
    }

    public function setLogger(LoggerInterface $logger): TeacherSubjectService
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * @param User $me
     * @param GetMyTeacherSubjectsDto $dto
     *
     * @return DataProviderInterface<MyTeacherSubject>
     */
    public function getMy(User $me, GetMyTeacherSubjectsDto $dto): DataProviderInterface
    {
        if (!in_array($dto->getSortBy(), self::GET_ALL_SORT, true)) {
            throw ValidationException::new([
                new ValidationError(
                    'sort_by',
                    ValidationErrorSlugEnum::WrongField->getSlug(),
                    sprintf('Сортировка доступна по полям: %s', implode(', ', self::GET_ALL_SORT)),
                ),
            ]);
        }
        if ($dto->getSubjectIds() === []) {
            return new EmptyDataProvider();
        }
        return $this
            ->teacherSubjectRepository
            ->findMy($me, $dto);
    }

    /**
     * @param GetAllTeacherSubjectsDto $dto
     *
     * @return DataProviderInterface<TeacherSubject>
     */
    public function getAll(GetAllTeacherSubjectsDto $dto): DataProviderInterface
    {
        if (!in_array($dto->getSortBy(), self::GET_ALL_SORT, true)) {
            throw ValidationException::new([
                new ValidationError(
                    'sort_by',
                    ValidationErrorSlugEnum::WrongField->getSlug(),
                    sprintf('Сортировка доступна по полям: %s', implode(', ', self::GET_ALL_SORT)),
                ),
            ]);
        }
        if ($dto->getSubjectIds() === [] || $dto->getTeacherIds() === []) {
            return new EmptyDataProvider();
        }
        return $this
            ->teacherSubjectRepository
            ->findAll($dto);
    }
}
