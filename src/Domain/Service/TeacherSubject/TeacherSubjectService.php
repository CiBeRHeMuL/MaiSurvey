<?php

namespace App\Domain\Service\TeacherSubject;

use App\Domain\DataProvider\DataProviderInterface;
use App\Domain\DataProvider\EmptyDataProvider;
use App\Domain\Dto\TeacherSubject\CreateTeacherSubjectDto;
use App\Domain\Dto\TeacherSubject\GetAllTeacherSubjectsDto;
use App\Domain\Dto\TeacherSubject\GetMyTeacherSubjectsDto;
use App\Domain\Dto\TeacherSubject\GetTSByIndexDto;
use App\Domain\Dto\TeacherSubject\GetTSByIndexRawDto;
use App\Domain\Entity\MyTeacherSubject;
use App\Domain\Entity\TeacherSubject;
use App\Domain\Entity\User;
use App\Domain\Enum\ValidationErrorSlugEnum;
use App\Domain\Exception\ErrorException;
use App\Domain\Exception\ValidationException;
use App\Domain\Repository\TeacherSubjectRepositoryInterface;
use App\Domain\Service\Db\TransactionManagerInterface;
use App\Domain\Validation\ValidationError;
use ArrayIterator;
use DateTimeImmutable;
use Iterator;
use Psr\Log\LoggerInterface;
use Throwable;

class TeacherSubjectService
{
    public const array GET_ALL_SORT = ['name', 'created_at'];

    private LoggerInterface $logger;

    public function __construct(
        private TeacherSubjectRepositoryInterface $teacherSubjectRepository,
        private TransactionManagerInterface $transactionManager,
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

    /**
     * @param CreateTeacherSubjectDto[] $dtos
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

            $created = $this->teacherSubjectRepository->createMulti($entities);

            if ($transaction) {
                $this->transactionManager->commit();
            }

            return $created;
        } catch (Throwable $e) {
            if (!$e instanceof ValidationError && !$e instanceof ErrorException) {
                $this->logger->error('An error occurred', ['exception' => $e]);
            }
            if ($transaction) {
                $this->transactionManager->rollback();
            }
            if ($throwOnError) {
                throw $e;
            } else {
                return 0;
            }
        }
    }

    /**
     * @param GetTSByIndexDto[] $indexes
     *
     * @return Iterator<int, TeacherSubject>
     */
    public function getAllByIndexes(array $indexes): Iterator
    {
        if ($indexes === []) {
            return new ArrayIterator([]);
        }
        return $this
            ->teacherSubjectRepository
            ->findAllByIndexes($indexes);
    }

    /**
     * @param GetTSByIndexRawDto[] $indexes
     *
     * @return Iterator<int, TeacherSubject>
     */
    public function getAllByRawIndexes(array $indexes): Iterator
    {
        if ($indexes === []) {
            return new ArrayIterator([]);
        }
        return $this
            ->teacherSubjectRepository
            ->findAllByRawIndexes($indexes);
    }

    private function entityFromCreateDto(CreateTeacherSubjectDto $dto): TeacherSubject
    {
        $teacherSubject = new TeacherSubject();
        $teacherSubject
            ->setTeacherId($dto->getTeacher()->getId())
            ->setSubjectId($dto->getSubject()->getId())
            ->setType($dto->getType())
            ->setTeacher($dto->getTeacher())
            ->setSubject($dto->getSubject())
            ->setCreatedAt(new DateTimeImmutable());
        return $teacherSubject;
    }

    public function validateCreateDto(CreateTeacherSubjectDto $dto, bool $checkExisting = true): void
    {
        if ($dto->getTeacher()->isActive() === false) {
            throw ValidationException::new([
                new ValidationError(
                    'teacher_id',
                    ValidationErrorSlugEnum::WrongField->getSlug(),
                    'Преподаватель должен быть активен',
                ),
            ]);
        }
        if ($dto->getTeacher()->isTeacher() === false) {
            throw ValidationException::new([
                new ValidationError(
                    'teacher_id',
                    ValidationErrorSlugEnum::WrongField->getSlug(),
                    'Преподаватель должен быть преподавателем',
                ),
            ]);
        }

        if ($checkExisting) {
            $existing = $this
                ->teacherSubjectRepository
                ->findAllByIndexes([
                    new GetTSByIndexDto(
                        $dto->getTeacher()->getId(),
                        $dto->getSubject()->getId(),
                        $dto->getType(),
                    ),
                ]);
            if (iterator_count($existing) > 0) {
                throw ValidationException::new([
                    new ValidationError(
                        'teacher_id',
                        ValidationErrorSlugEnum::AlreadyExists->getSlug(),
                        'Этот преподаватель уже ведет этот предмет',
                    ),
                    new ValidationError(
                        'subject_id',
                        ValidationErrorSlugEnum::AlreadyExists->getSlug(),
                        'Этот преподаватель уже ведет этот предмет',
                    ),
                    new ValidationError(
                        'type',
                        ValidationErrorSlugEnum::AlreadyExists->getSlug(),
                        'Этот преподаватель уже ведет этот предмет',
                    ),
                ]);
            }
        }
    }
}
