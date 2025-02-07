<?php

namespace App\Domain\Service\StudentSubject;

use App\Domain\DataProvider\DataProviderInterface;
use App\Domain\Dto\StudentSubject\CreateStudentSubjectDto;
use App\Domain\Dto\StudentSubject\GetAllStudentSubjectsDto;
use App\Domain\Dto\StudentSubject\GetMyStudentSubjectsDto;
use App\Domain\Dto\StudentSubject\GetSSByIndexDto;
use App\Domain\Dto\StudentSubject\GetSSByIndexRawDto;
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
                    'sort_by',
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
     * @param GetSSByIndexDto $index
     *
     * @return StudentSubject|null
     */
    public function getByIndex(GetSSByIndexDto $index): StudentSubject|null
    {
        return $this
            ->userSubjectRepository
            ->findByIndex($index);
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

        if ($checkExisting) {
            $existing = $this
                ->getByIndex(
                    new GetSSByIndexDto(
                        $dto->getStudent()->getId(),
                        $dto->getTeacherSubject()->getId(),
                    ),
                );
            if ($existing !== null) {
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

    /**
     * @param GetSSByIndexRawDto[] $indexes
     *
     * @return Iterator<int, StudentSubject>
     */
    public function getAllByRawIndexes(array $indexes): Iterator
    {
        if ($indexes === []) {
            return new ArrayIterator();
        }
        return $this
            ->userSubjectRepository
            ->findAllByRawIndexes($indexes);
    }

    private function entityFromCreateDto(CreateStudentSubjectDto $dto): StudentSubject
    {
        $studentSubject = new StudentSubject();
        return $studentSubject
            ->setUserId($dto->getStudent()->getId())
            ->setTeacherSubjectId($dto->getTeacherSubject()->getId())
            ->setCreatedAt(new DateTimeImmutable())
            ->setUpdatedAt(new DateTimeImmutable())
            ->setUser($dto->getStudent())
            ->setTeacherSubject($dto->getTeacherSubject());
    }
}
