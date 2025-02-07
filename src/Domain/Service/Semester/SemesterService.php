<?php

namespace App\Domain\Service\Semester;

use App\Domain\DataProvider\DataProviderInterface;
use App\Domain\Dto\Semester\CreateSemesterDto;
use App\Domain\Dto\Semester\GetAllSemestersDto;
use App\Domain\Dto\Semester\GetSemesterByIndexDto;
use App\Domain\Entity\Semester;
use App\Domain\Enum\ValidationErrorSlugEnum;
use App\Domain\Exception\ValidationException;
use App\Domain\Repository\SemesterRepositoryInterface;
use App\Domain\Service\Db\TransactionManagerInterface;
use App\Domain\Validation\ValidationError;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;
use Throwable;

class SemesterService
{
    const array GET_ALL_SORT = ['year'];
    private LoggerInterface $logger;

    public function __construct(
        private SemesterRepositoryInterface $semesterRepository,
        private TransactionManagerInterface $transactionManager,
        LoggerInterface $logger,
    ) {
        $this->setLogger($logger);
    }

    public function setLogger(LoggerInterface $logger): SemesterService
    {
        $this->logger = $logger;
        return $this;
    }

    public function create(CreateSemesterDto $dto): Semester
    {
        $this->validateCreateDto($dto);

        $entity = $this->entityFromCreateDto($dto);

        $this->semesterRepository->create($entity);

        return $entity;
    }

    public function validateCreateDto(CreateSemesterDto $dto, bool $checkExisting = true): void
    {
        if ($checkExisting) {
            $existing = $this
                ->semesterRepository
                ->findAllByIndexes([new GetSemesterByIndexDto($dto->getYear(), $dto->isSpring())]);
            if ($existing !== []) {
                throw ValidationException::new([
                    new ValidationError(
                        'year',
                        ValidationErrorSlugEnum::AlreadyExists->getSlug(),
                        'Такой семестр уже существует',
                    ),
                    new ValidationError(
                        'spring',
                        ValidationErrorSlugEnum::AlreadyExists->getSlug(),
                        'Такой семестр уже существует',
                    ),
                ]);
            }
        }
    }

    public function entityFromCreateDto(CreateSemesterDto $dto): Semester
    {
        $entity = new Semester();
        $entity
            ->setYear($dto->getYear())
            ->setSpring($dto->isSpring())
            ->setCreatedAt(new DateTimeImmutable());
        return $entity;
    }

    /**
     * @param CreateSemesterDto[] $dtos
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

            $created = $this->semesterRepository->createMulti($entities);

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

    /**
     * @return DataProviderInterface<Semester>
     */
    public function getAll(GetAllSemestersDto $dto): DataProviderInterface
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
            ->semesterRepository
            ->findAll($dto);
    }

    public function getById(Uuid $semesterId): Semester|null
    {
        return $this
            ->semesterRepository
            ->findById($semesterId);
    }

    /**
     * @param GetSemesterByIndexDto[] $indexes
     *
     * @return Semester[]
     */
    public function getByIndexes(array $indexes): array
    {
        return $this
            ->semesterRepository
            ->findAllByIndexes($indexes);
    }
}
