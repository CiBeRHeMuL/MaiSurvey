<?php

namespace App\Domain\Service\UserData;

use App\Domain\DataProvider\DataProviderInterface;
use App\Domain\Dto\UserData\CreateUserDataDto;
use App\Domain\Dto\UserData\GetAllUserDataDto;
use App\Domain\Dto\UserDataGroup\CreateUserDataGroupDto;
use App\Domain\Entity\UserData;
use App\Domain\Enum\RoleEnum;
use App\Domain\Enum\ValidationErrorSlugEnum;
use App\Domain\Exception\ErrorException;
use App\Domain\Exception\ValidationException;
use App\Domain\Repository\UserDataRepositoryInterface;
use App\Domain\Service\Db\TransactionManagerInterface;
use App\Domain\Service\UserDataGroup\UserDataGroupService;
use App\Domain\Validation\ValidationError;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;
use Throwable;

class UserDataService
{
    public const array GET_ALL_SORT = ['name'];

    private LoggerInterface $logger;

    public function __construct(
        private UserDataRepositoryInterface $userDataRepository,
        private TransactionManagerInterface $transactionManager,
        private UserDataGroupService $userDataGroupService,
        LoggerInterface $logger,
    ) {
        $this->setLogger($logger);
    }

    public function setLogger(LoggerInterface $logger): UserDataService
    {
        $this->logger = $logger;
        $this->userDataGroupService->setLogger($logger);
        return $this;
    }

    public function getById(Uuid $id): UserData|null
    {
        return $this
            ->userDataRepository
            ->findById($id);
    }

    public function getByUserId(Uuid $userId): UserData|null
    {
        return $this
            ->userDataRepository
            ->findByUserId($userId);
    }

    /**
     * Получить список данных пользователей с учетом фильтров и пагинации.
     *
     * @param GetAllUserDataDto $dto
     *
     * @return DataProviderInterface<UserData>
     */
    public function getAll(GetAllUserDataDto $dto): DataProviderInterface
    {
        if ($dto->getName() !== null && mb_strlen($dto->getName()) < 3) {
            throw ValidationException::new([
                new ValidationError(
                    'name',
                    ValidationErrorSlugEnum::WrongField->getSlug(),
                    'Имя должно быть не короче 3 символов',
                ),
            ]);
        }

        if (!in_array($dto->getSortBy(), self::GET_ALL_SORT, true)) {
            throw ValidationException::new([
                new ValidationError(
                    'name',
                    ValidationErrorSlugEnum::WrongField->getSlug(),
                    sprintf('Сортировка доступна по полям: %s', implode(', ', self::GET_ALL_SORT)),
                ),
            ]);
        }

        if ($dto->getForRole()?->isMain() === false) {
            throw ValidationException::new([
                new ValidationError(
                    'for_role',
                    ValidationErrorSlugEnum::WrongField->getSlug(),
                    'Эта роль недоступна для поиска',
                ),
            ]);
        }

        return $this->userDataRepository->findAll($dto);
    }

    public function update(UserData $userData): bool
    {
        $userData
            ->setUpdatedAt(new DateTimeImmutable());
        return $this
            ->userDataRepository
            ->update($userData);
    }

    public function create(CreateUserDataDto $dto): UserData
    {
        $this->validateCreateDto($dto);
        try {
            $this->transactionManager->beginTransaction();

            $userData = $this->entityFromDto($dto);

            if ($this->userDataRepository->create($userData) === false) {
                throw ErrorException::new(
                    'Не удалось сохранить данные пользователя',
                    400,
                );
            }

            if ($dto->getGroup() !== null) {
                $userDataGroup = $this
                    ->userDataGroupService
                    ->create(
                        new CreateUserDataGroupDto(
                            $userData,
                            $dto->getGroup(),
                        ),
                    );
                $userData
                    ->setGroup($userDataGroup);
            }

            $this->transactionManager->commit();
            return $userData;
        } catch (ErrorException|ValidationException $e) {
            $this->transactionManager->rollback();
            throw $e;
        } catch (Throwable $e) {
            $this->logger->error($e);
            $this->transactionManager->rollback();
            throw $e;
        }
    }

    public function validateCreateDto(CreateUserDataDto $dto): void
    {
        if ($dto->getFirstName() === '') {
            throw ValidationException::new([
                new ValidationError(
                    'name',
                    ValidationErrorSlugEnum::AlreadyExists->getSlug(),
                    'Имя не должно быть пустым',
                ),
            ]);
        }
        if ($dto->getLastName() === '') {
            throw ValidationException::new([
                new ValidationError(
                    'name',
                    ValidationErrorSlugEnum::AlreadyExists->getSlug(),
                    'Фамилия не должна быть пустой',
                ),
            ]);
        }
        if ($dto->getPatronymic() === '') {
            throw ValidationException::new([
                new ValidationError(
                    'name',
                    ValidationErrorSlugEnum::AlreadyExists->getSlug(),
                    'Отчество не должно быть пустым',
                ),
            ]);
        }

        if ($dto->getRole()->isMain() === false) {
            throw ValidationException::new([
                new ValidationError(
                    'role',
                    ValidationErrorSlugEnum::WrongField->getSlug(),
                    'Нельзя создать данные для не основной роли',
                ),
            ]);
        }

        if ($dto->getRole() === RoleEnum::Student && $dto->getGroup() === null) {
            throw ValidationException::new([
                new ValidationError(
                    'group',
                    ValidationErrorSlugEnum::WrongField->getSlug(),
                    'Для создания студента укажите группу',
                ),
            ]);
        }
        if ($dto->getRole() !== RoleEnum::Student && $dto->getGroup() !== null) {
            throw ValidationException::new([
                new ValidationError(
                    'group',
                    ValidationErrorSlugEnum::WrongField->getSlug(),
                    'Нельзя привязать группу к не студенту',
                ),
            ]);
        }
    }

    /**
     * Массовое создание данных
     *
     * @param CreateUserDataDto[] $dtos
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

                if ($dto->getGroup() !== null) {
                    $groupDtos[] = new CreateUserDataGroupDto($entity, $dto->getGroup());
                }
            }

            $created = $this->userDataRepository->createMulti($entities);

            $this->userDataGroupService->createMulti($groupDtos, false, true, $throwOnError);

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

    private function entityFromDto(CreateUserDataDto $dto): UserData
    {
        $userData = new UserData();
        $userData
            ->setFirstName($dto->getFirstName())
            ->setLastName($dto->getLastName())
            ->setPatronymic($dto->getPatronymic())
            ->setForRole($dto->getRole())
            ->setCreatedAt(new DateTimeImmutable())
            ->setUpdatedAt(new DateTimeImmutable());
        return $userData;
    }
}
