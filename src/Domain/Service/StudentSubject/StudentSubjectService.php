<?php

namespace App\Domain\Service\StudentSubject;

use App\Domain\DataProvider\DataProviderInterface;
use App\Domain\Dto\StudentSubject\CreateStudentSubjectDto;
use App\Domain\Dto\StudentSubject\GetAllStudentSubjectsDto;
use App\Domain\Dto\StudentSubject\GetMyStudentSubjectsDto;
use App\Domain\Dto\StudentSubject\GetStudentSubjectByIntersectionDto;
use App\Domain\Entity\StudentSubject;
use App\Domain\Entity\User;
use App\Domain\Enum\ValidationErrorSlugEnum;
use App\Domain\Exception\ValidationException;
use App\Domain\Repository\StudentSubjectRepositoryInterface;
use App\Domain\Service\Db\TransactionManagerInterface;
use App\Domain\Validation\ValidationError;
use ArrayIterator;
use DateTimeImmutable;
use Iterator;
use Psr\Log\LoggerInterface;
use Throwable;

class StudentSubjectService
{
    public const array GET_ALL_SORT = ['name', 'actual_from', 'actual_to'];

    private LoggerInterface $logger;

    public function __construct(
        LoggerInterface $logger,
        private StudentSubjectRepositoryInterface $userSubjectRepository,
        private TransactionManagerInterface $transactionManager,
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
                    'sort_by',
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

    /**
     * @param GetStudentSubjectByIntersectionDto[] $intersections
     *
     * @return Iterator<int, StudentSubject>
     */
    public function getAllByIntersections(array $intersections): Iterator
    {
        if ($intersections === []) {
            return new ArrayIterator();
        }
        return $this
            ->userSubjectRepository
            ->findAllByIntersections($intersections);
    }

    /**
     * @param CreateStudentSubjectDto[] $dtos
     * @param bool $validate
     * @param bool $transaction
     * @param bool $throwOnError
     *
     * @return int
     * @throws Throwable
     */
    public function createMulti(array $dtos, bool $validate = true, bool $transaction = true, bool $throwOnError = false): int
    {
        if ($dtos === []) {
            return 0;
        }

        if ($transaction) {
            $this->transactionManager->beginTransaction();
        }

        try {
            $entities = [];
            foreach ($dtos as $dto) {
                if ($validate) {
                    $this->validateCreateDto($dto);
                }

                $entities[] = $this->entityFromCreateDto($dto);
            }

            $created = $this->userSubjectRepository->createMulti($entities);

            if ($transaction) {
                $this->transactionManager->commit();
            }

            return $created;
        } catch (Throwable $e) {
            $this->logger->error($e);
            $this->transactionManager->rollback();
            if ($throwOnError) {
                throw $e;
            } else {
                return 0;
            }
        }
    }

    public function validateCreateDto(CreateStudentSubjectDto $dto, bool $checkExisting = true): void
    {
        if ($dto->getStudent()->isActive() === false) {
            throw ValidationException::new([
                new ValidationError(
                    'user_id',
                    ValidationErrorSlugEnum::WrongField->getSlug(),
                    'Студент должен быть активен',
                ),
            ]);
        }
        if ($dto->getStudent()->isStudent() === false) {
            throw ValidationException::new([
                new ValidationError(
                    'user_id',
                    ValidationErrorSlugEnum::WrongField->getSlug(),
                    'Студент должен быть студентом',
                ),
            ]);
        }
        if ($dto->getActualTo()->getTimestamp() <= $dto->getActualFrom()->getTimestamp()) {
            throw ValidationException::new([
                new ValidationError(
                    'actual_from',
                    ValidationErrorSlugEnum::WrongField->getSlug(),
                    'Временной промежуток действия предмета должен быть корректным',
                ),
                new ValidationError(
                    'actual_to',
                    ValidationErrorSlugEnum::WrongField->getSlug(),
                    'Временной промежуток действия предмета должен быть корректным',
                ),
            ]);
        }

        if ($checkExisting) {
            $existing = $this
                ->getAllByIntersections([
                    new GetStudentSubjectByIntersectionDto(
                        $dto->getStudent()->getId(),
                        $dto->getTeacherSubject()->getId(),
                        $dto->getActualFrom(),
                        $dto->getActualTo(),
                    ),
                ]);
            if (iterator_count($existing)) {
                throw ValidationException::new([
                    new ValidationError(
                        'user_id',
                        ValidationErrorSlugEnum::WrongField->getSlug(),
                        'Этот студент уже ходит на этот предмет',
                    ),
                    new ValidationError(
                        'teacher_subject_id',
                        ValidationErrorSlugEnum::WrongField->getSlug(),
                        'Этот студент уже ходит на этот предмет',
                    ),
                ]);
            }
        }
    }

    private function entityFromCreateDto(CreateStudentSubjectDto $dto): StudentSubject
    {
        $studentSubject = new StudentSubject();
        return $studentSubject
            ->setUserId($dto->getStudent()->getId())
            ->setTeacherSubjectId($dto->getTeacherSubject()->getId())
            ->setActualFrom($dto->getActualFrom())
            ->setActualTo($dto->getActualTo())
            ->setCreatedAt(new DateTimeImmutable())
            ->setUpdatedAt(new DateTimeImmutable())
            ->setUser($dto->getStudent())
            ->setTeacherSubject($dto->getTeacherSubject());
    }
}
