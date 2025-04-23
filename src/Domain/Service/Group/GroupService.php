<?php

namespace App\Domain\Service\Group;

use App\Domain\DataProvider\DataProviderInterface;
use App\Domain\Dto\Group\CreateGroupDto;
use App\Domain\Dto\Group\GetAllGroupsDto;
use App\Domain\Entity\Group;
use App\Domain\Enum\ValidationErrorSlugEnum;
use App\Domain\Exception\ErrorException;
use App\Domain\Exception\ValidationException;
use App\Domain\Repository\GroupRepositoryInterface;
use App\Domain\Service\Db\TransactionManagerInterface;
use App\Domain\Validation\ValidationError;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;
use Throwable;

class GroupService
{
    public const array GET_ALL_SORT = ['name'];

    private LoggerInterface $logger;

    public function __construct(
        private GroupRepositoryInterface $groupRepository,
        private TransactionManagerInterface $transactionManager,
        LoggerInterface $logger,
    ) {
        $this->setLogger($logger);
    }

    public function setLogger(LoggerInterface $logger): GroupService
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * Получить список групп с учетом фильтров и пагинации.
     *
     * @param GetAllGroupsDto $dto
     *
     * @return DataProviderInterface<Group>
     */
    public function getAll(GetAllGroupsDto $dto): DataProviderInterface
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

        return $this->groupRepository->findAll($dto);
    }

    public function getByName(string $name): Group|null
    {
        return $this
            ->groupRepository
            ->findByName($name);
    }

    public function create(CreateGroupDto $dto): Group
    {
        $this->validateCreateDto($dto);

        $group = $this->entityFromDto($dto);
        if ($this->groupRepository->create($group) === false) {
            throw ErrorException::new(
                'Не удалось сохранить группу',
                400,
            );
        }
        return $group;
    }

    public function getById(Uuid $id): Group|null
    {
        return $this
            ->groupRepository
            ->findById($id);
    }

    /**
     * Получить группы по списку названий
     *
     * @param string[] $groupNames
     *
     * @return Group[]
     */
    public function getByNames(array $groupNames): array
    {
        if (!$groupNames) {
            return [];
        }
        return $this
            ->groupRepository
            ->findByNames($groupNames);
    }

    /**
     * Массовое создание групп
     *
     * @param CreateGroupDto[] $dtos
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

            $created = $this->groupRepository->createMulti($entities);

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

    public function validateCreateDto(CreateGroupDto $dto, bool $checkExisting = true): void
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
                ->groupRepository
                ->findByName($dto->getName());
            if ($existing !== null) {
                throw ValidationException::new([
                    new ValidationError(
                        'name',
                        ValidationErrorSlugEnum::AlreadyExists->getSlug(),
                        'Группа уже существует',
                    ),
                ]);
            }
        }
    }

    private function entityFromDto(CreateGroupDto $dto): Group
    {
        $subject = new Group();
        $subject
            ->setName(mb_strtoupper(trim($dto->getName())))
            ->setCreatedAt(new DateTimeImmutable())
            ->setUpdatedAt(new DateTimeImmutable());
        return $subject;
    }
}
