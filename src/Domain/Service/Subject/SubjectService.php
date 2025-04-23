<?php

namespace App\Domain\Service\Subject;

use App\Domain\DataProvider\DataProviderInterface;
use App\Domain\Dto\Subject\CreateSubjectDto;
use App\Domain\Dto\Subject\GetAllSubjectsDto;
use App\Domain\Dto\Subject\GetByRawIndexDto;
use App\Domain\Entity\Subject;
use App\Domain\Enum\ValidationErrorSlugEnum;
use App\Domain\Exception\ErrorException;
use App\Domain\Exception\ValidationException;
use App\Domain\Repository\SubjectRepositoryInterface;
use App\Domain\Service\Db\TransactionManagerInterface;
use App\Domain\Validation\ValidationError;
use DateTimeImmutable;
use Iterator;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;
use Throwable;

class SubjectService
{
    public const array GET_ALL_SORT = ['name'];

    private LoggerInterface $logger;

    public function __construct(
        private SubjectRepositoryInterface $subjectRepository,
        private TransactionManagerInterface $transactionManager,
        LoggerInterface $logger,
    ) {
        $this->setLogger($logger);
    }

    public function setLogger(LoggerInterface $logger): SubjectService
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * Получить список предметов с учетом фильтров и пагинации.
     *
     * @param GetAllSubjectsDto $dto
     *
     * @return DataProviderInterface<Subject>
     */
    public function getAll(GetAllSubjectsDto $dto): DataProviderInterface
    {
        if ($dto->getName() !== null && mb_strlen($dto->getName()) < 3) {
            throw ValidationException::new([
                new ValidationError(
                    'name',
                    ValidationErrorSlugEnum::WrongField->getSlug(),
                    'Название должно быть не короче 3 символов',
                ),
            ]);
        }

        if (!in_array($dto->getSortBy(), self::GET_ALL_SORT, true)) {
            throw ValidationException::new([
                new ValidationError(
                    'sort_by',
                    ValidationErrorSlugEnum::WrongField->getSlug(),
                    sprintf('Сортировка доступна по полям: %s', implode(', ', self::GET_ALL_SORT)),
                ),
            ]);
        }

        return $this->subjectRepository->findAll($dto);
    }

    public function create(CreateSubjectDto $dto): Subject
    {
        $this->validateCreateDto($dto);

        $subject = $this->entityFromDto($dto);
        if ($this->subjectRepository->create($subject) === false) {
            throw ErrorException::new(
                'Не удалось сохранить предмет',
                400,
            );
        }
        return $subject;
    }

    public function getById(Uuid $id): Subject|null
    {
        return $this
            ->subjectRepository
            ->findById($id);
    }

    /**
     * Получить предметы по списку названий
     *
     * @param GetByRawIndexDto[] $indexes
     *
     * @return Iterator<int, Subject>
     */
    public function getByRawIndexes(array $indexes): Iterator
    {
        return $this
            ->subjectRepository
            ->findByRawIndexes($indexes);
    }

    /**
     * Массовое создание предметов
     *
     * @param CreateSubjectDto[] $dtos
     * @param bool $validate валидировать данные?
     * @param bool $transaction выполнять в транзакции?
     * @param bool $throwOnError
     *
     * @return int
     * @throws Throwable
     */
    public function createMulti(array $dtos, bool $validate = true, bool $transaction = true, bool $throwOnError = false): int
    {
        if ($transaction) {
            $this->transactionManager->beginTransaction();
        }
        try {
            $groupDtos = [];
            $entities = [];
            foreach ($dtos as $dto) {
                if ($validate) {
                    $this->validateCreateDto($dto);
                }

                $entity = $this->entityFromDto($dto);
                $entities[] = $entity;
            }

            $created = $this->subjectRepository->createMulti($entities);

            if ($transaction) {
                $this->transactionManager->commit();
            }

            return $created;
        } catch (Throwable $e) {
            $this->logger->error($e);
            if ($transaction) {
                $this->transactionManager->rollback();
            }
            if ($throwOnError) {
                throw $e;
            }
            return 0;
        }
    }

    public function validateCreateDto(CreateSubjectDto $dto, bool $checkExisting = true): void
    {
        if ($dto->getName() === '') {
            throw ValidationException::new([
                new ValidationError(
                    'name',
                    ValidationErrorSlugEnum::AlreadyExists->getSlug(),
                    'Название не должно быть пустым',
                ),
            ]);
        }

        if ($checkExisting) {
            $existing = $this
                ->subjectRepository
                ->findByIndex($dto->getName(), $dto->getSemester()->getId());
            if ($existing !== null) {
                throw ValidationException::new([
                    new ValidationError(
                        'name',
                        ValidationErrorSlugEnum::AlreadyExists->getSlug(),
                        'Предмет уже существует',
                    ),
                ]);
            }
        }
    }

    private function entityFromDto(CreateSubjectDto $dto): Subject
    {
        $subject = new Subject();
        $subject
            ->setName(mb_ucfirst(trim($dto->getName())))
            ->setSemesterId($dto->getSemester()->getId())
            ->setSemester($dto->getSemester())
            ->setCreatedAt(new DateTimeImmutable())
            ->setUpdatedAt(new DateTimeImmutable());
        return $subject;
    }
}
