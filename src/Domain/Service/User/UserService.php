<?php

namespace App\Domain\Service\User;

use App\Domain\DataProvider\ArrayDataProvider;
use App\Domain\DataProvider\DataProviderInterface;
use App\Domain\DataProvider\DataSortInterface;
use App\Domain\Dto\Me\UpdateMeDto;
use App\Domain\Dto\User\CreateUserDto;
use App\Domain\Dto\User\GetAllUsersDto;
use App\Domain\Dto\User\UpdateUserDto;
use App\Domain\Dto\UserData\UpdateUserDataDto;
use App\Domain\Entity\User;
use App\Domain\Enum\RoleEnum;
use App\Domain\Enum\ValidationErrorSlugEnum;
use App\Domain\Exception\ErrorException;
use App\Domain\Exception\ValidationException;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\Service\Db\TransactionManagerInterface;
use App\Domain\Service\Security\EmailCheckerServiceInterface;
use App\Domain\Service\Security\PasswordHasherServiceInterface;
use App\Domain\Service\Security\SecurityService;
use App\Domain\Service\SurveyStat\StatRefresherInterface;
use App\Domain\Service\UserData\UserDataService;
use App\Domain\Validation\ValidationError;
use App\Domain\ValueObject\Email;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;
use SensitiveParameter;
use Symfony\Component\Uid\Uuid;
use Throwable;

class UserService
{
    public const array GET_ALL_SORT = ['name', 'created_at', 'deleted', 'email'];

    private LoggerInterface $logger;

    public function __construct(
        private UserRepositoryInterface $userRepository,
        private EmailCheckerServiceInterface $emailCheckerService,
        private SecurityService $securityService,
        private PasswordHasherServiceInterface $passwordHasherService,
        private TransactionManagerInterface $transactionManager,
        private UserDataService $userDataService,
        private StatRefresherInterface $statRefresher,
        LoggerInterface $logger,
    ) {
        $this->setLogger($logger);
    }

    public function setLogger(LoggerInterface $logger): UserService
    {
        $this->logger = $logger;
        $this->userDataService->setLogger($logger);
        $this->statRefresher->setLogger($logger);
        return $this;
    }

    public function getById(Uuid $id): User|null
    {
        return $this
            ->userRepository
            ->findById($id);
    }

    public function refreshCredentials(User $user): User
    {
        $accessToken = $this->securityService->generateAccessToken();
        $refreshToken = $this->securityService->generateRefreshToken();
        $user
            ->setAccessToken($accessToken->getToken())
            ->setAccessTokenExpiresAt($accessToken->getExpiresAt())
            ->setRefreshToken($refreshToken->getToken())
            ->setRefreshTokenExpiresAt($refreshToken->getExpiresAt())
            ->setUpdatedAt(new DateTimeImmutable());
        if ($this->userRepository->update($user) === false) {
            throw ErrorException::new(
                'Ошибка генерации токена доступа, обратитесь в службу поддержки',
            );
        }
        return $user;
    }

    public function refreshCredentialsIfNeeded(User $user): User
    {
        $expiresAt = $user->getAccessTokenExpiresAt();
        if ($expiresAt->getTimestamp() <= (new DateTimeImmutable())->getTimestamp()) {
            $this->refreshCredentials($user);
        }
        return $user;
    }

    public function create(CreateUserDto $dto, bool $checkExisting = true): User
    {
        $this->validateCreateDto($dto, $checkExisting);

        $user = $this->entityFromCreateDto($dto);

        if ($this->userRepository->create($user) === false) {
            throw ErrorException::new(
                'Ошибка создания пользователя, обратитесь в службу поддержки',
            );
        }
        return $user;
    }

    public function getByEmail(Email $email): User|null
    {
        return $this
            ->userRepository
            ->findByEmail($email);
    }

    public function saveUpdates(User $user): bool
    {
        $user->setUpdatedAt(new DateTimeImmutable());
        return $this->userRepository->update($user);
    }

    /**
     * Получить список пользователей с пагинацией и сортировкой
     *
     * @param GetAllUsersDto $dto
     *
     * @return DataProviderInterface<User>
     */
    public function getAll(GetAllUsersDto $dto): DataProviderInterface
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
                    'sort_by',
                    ValidationErrorSlugEnum::WrongField->getSlug(),
                    sprintf('Сортировка доступна по полям: %s', implode(', ', self::GET_ALL_SORT)),
                ),
            ]);
        }

        if (
            $dto->getCreatedFrom() !== null
            && $dto->getCreatedTo() !== null
            && $dto->getCreatedTo()->getTimestamp() < $dto->getCreatedFrom()->getTimestamp()
        ) {
            throw ValidationException::new([
                new ValidationError(
                    'created_from',
                    ValidationErrorSlugEnum::WrongField->getSlug(),
                    'Дата начала должна быть меньше даты окончания',
                ),
            ]);
        }

        return $this
            ->userRepository
            ->findAll($dto);
    }

    /**
     * Поиск по почтам
     *
     * @param Email[] $emails
     *
     * @return User[]
     */
    public function getAllByEmails(array $emails): array
    {
        if (!$emails) {
            return [];
        }
        return $this
            ->userRepository
            ->findAllByEmails($emails);
    }

    public function validateCreateDto(CreateUserDto $dto, bool $checkExisting = true): void
    {
        if ($checkExisting) {
            $exists = $this
                ->userRepository
                ->findByEmail($dto->getEmail());
            if ($exists) {
                throw ValidationException::new(
                    [
                        new ValidationError(
                            'email',
                            ValidationErrorSlugEnum::AlreadyExists->getSlug(),
                            'Пользователь с такой почтой уже существует',
                        ),
                    ],
                );
            }
        }
        $this->emailCheckerService->checkEmail($dto->getEmail());
    }

    /**
     * @param CreateUserDto[] $dtos
     * @param bool $validate
     * @param bool $transaction
     * @param bool $throwOnError
     *
     * @return int
     */
    public function createMulti(
        array $dtos,
        bool $validate = true,
        bool $transaction = false,
        bool $throwOnError = false,
    ): int {
        try {
            if ($transaction) {
                $this->transactionManager->beginTransaction();
            }

            /** @var User[] $users */
            $users = [];
            foreach ($dtos as $dto) {
                if ($validate) {
                    $this->validateCreateDto($dto);
                }
                $users[] = $this->entityFromCreateDto($dto);
            }

            $created = $this->userRepository->createMulti($users);

            if ($transaction) {
                $this->transactionManager->commit();
            }
            return $created;
        } catch (ValidationException|ErrorException $e) {
            if ($transaction) {
                $this->transactionManager->rollback();
            }
            if ($throwOnError) {
                throw $e;
            }
            return 0;
        } catch (Throwable $e) {
            $this->logger->error('An error occurred', ['exception' => $e]);
            if ($transaction) {
                $this->transactionManager->rollback();
            }
            if ($throwOnError) {
                throw $e;
            }
            return 0;
        }
    }

    /**
     * @param string[] $emails
     *
     * @return string[]
     */
    public function getEmailsByEmails(array $emails): array
    {
        if (empty($emails)) {
            return [];
        }
        return $this
            ->userRepository
            ->findEmailsByEmails($emails);
    }

    public function getILikeEmails(array $emails): array
    {
        return $this
            ->userRepository
            ->findILikeEmails($emails);
    }

    public function deleteMe(User $me): User
    {
        if ($me->isDeleted()) {
            throw ErrorException::new('Нельзя удалить профиль', 400);
        }
        $me->setDeleted(true)
            ->setDeletedAt(new DateTimeImmutable());
        if ($this->userRepository->update($me)) {
            $this->statRefresher->refreshStats(force: true);
            return $me;
        }
        throw ErrorException::new('Не удалось удалить профиль');
    }

    private function entityFromCreateDto(CreateUserDto $dto): User
    {
        $accessToken = $this->securityService->generateAccessToken();
        $refreshToken = $this->securityService->generateRefreshToken();

        $user = new User();
        $user->setEmail($dto->getEmail())
            ->setAccessToken($accessToken->getToken())
            ->setAccessTokenExpiresAt($accessToken->getExpiresAt())
            ->setRefreshToken($refreshToken->getToken())
            ->setRefreshTokenExpiresAt($refreshToken->getExpiresAt())
            ->setCreatedAt(new DateTimeImmutable())
            ->setUpdatedAt(new DateTimeImmutable())
            ->setStatus($dto->getStatus())
            ->setRoles([$dto->getRole()])
            ->setPassword($this->passwordHasherService->hashPassword($dto->getPassword()))
            ->setDeletedAt(null)
            ->setDeleted(false)
            ->setNeedChangePassword($dto->isNeedChangePassword())
            ->setTelegramConnectId(Uuid::v4())
            ->setNoticesEnabled(false)
            ->setNoticeTypes([])
            ->setNoticeChannels([]);
        return $user;
    }

    public function getLastN(int $count, DataSortInterface $sort): DataProviderInterface
    {
        if ($count <= 0) {
            return new ArrayDataProvider([]);
        }
        return $this
            ->userRepository
            ->findLastN($count, $sort);
    }

    public function updateUser(User $user, UpdateUserDto $dto, User $updater): User
    {
        if ($updater->isAdmin() === false) {
            throw ErrorException::new('Вам запрещено выполнять это действие', 403);
        }

        $availableRoles = array_values(
            array_unique(
                array_merge(
                    $user->getRoles(),
                    ...array_map(
                        fn(RoleEnum $r) => $r->getAvailableAdditionalRoles(),
                        $user->getRoles(),
                    ),
                ),
                SORT_REGULAR,
            ),
        );

        $mainRole = $user->getMainRole();
        $mainRoleExists = false;
        foreach ($dto->getRoles() as $role) {
            if (!in_array($role, $availableRoles, true)) {
                throw ValidationException::new([
                    new ValidationError(
                        'roles',
                        ValidationErrorSlugEnum::WrongField->getSlug(),
                        sprintf(
                            'Нельзя присвоить роль %s пользователю. Выберите одну из следующих ролей: %s',
                            $role->getName(),
                            implode(', ', array_map(fn(RoleEnum $r) => $r->getName(), $availableRoles)),
                        ),
                    ),
                ]);
            }
            if ($role === $mainRole) {
                $mainRoleExists = true;
            }
        }
        if (!$mainRoleExists) {
            throw ValidationException::new([
                new ValidationError(
                    'roles',
                    ValidationErrorSlugEnum::WrongField->getSlug(),
                    sprintf('Нельзя удалить роль %s у пользователя', $user->getMainRole()->getName()),
                ),
            ]);
        }

        $availableStatuses = array_values(
            array_unique(
                array_merge(
                    $user->getStatus()->getAvailableStatuses(),
                    [$user->getStatus()],
                ),
                SORT_REGULAR,
            ),
        );
        if (!in_array($dto->getStatus(), $availableStatuses, true)) {
            throw ValidationException::new([
                new ValidationError(
                    'status',
                    ValidationErrorSlugEnum::WrongField->getSlug(),
                    'Нельзя поменять статус пользователя на выбранный',
                ),
            ]);
        }

        if ($user->isNeedChangePassword() && $dto->isNeedChangePassword() === false) {
            throw ValidationException::new([
                new ValidationError(
                    'need_change_password',
                    ValidationErrorSlugEnum::WrongField->getSlug(),
                    'Нельзя отменить необходимость сменить пароль',
                )
            ]);
        }

        $user->setStatus($dto->getStatus())
            ->setRoles($dto->getRoles())
            ->setUpdaterId($updater->getId())
            ->setUpdater($updater)
            ->setUpdatedAt(new DateTimeImmutable())
            ->setNeedChangePassword($dto->isNeedChangePassword());

        $updated = $this->userRepository->update($user);
        if (!$updated) {
            throw ErrorException::new('Не удалось обновить пользователя');
        }
        return $user;
    }

    public function updateMe(User $me, UpdateMeDto $dto): User
    {
        if (!$me->isActive()) {
            throw ErrorException::new('Действие запрещено', 403);
        }

        try {
            $this->transactionManager->beginTransaction();

            $this->validateUpdateMeDto($dto);

            $me->setUpdatedAt(new DateTimeImmutable())
                ->setNoticesEnabled($dto->isNoticesEnabled())
                ->setNoticeTypes($dto->getNoticeTypes())
                ->setNoticeChannels($dto->getNoticeChannels())
                ->setUpdater($me)
                ->setUpdaterId($me->getId());
            $updated = $this->userRepository->update($me);
            if (!$updated) {
                throw ErrorException::new('Не удалось обновить запись');
            }

            $data = $me->getData();

            $data = $this->userDataService
                ->update(
                    $data,
                    new UpdateUserDataDto(
                        $dto->getFirstName(),
                        $dto->getLastName(),
                        $dto->getPatronymic(),
                        $data->getGroup()?->getGroup(),
                    ),
                );
            $me->setData($data);

            $this->transactionManager->commit();
            return $me;
        } catch (Throwable $e) {
            if ($e instanceof ValidationException || $e instanceof ErrorException) {
                $this->transactionManager->rollback();
                throw $e;
            }
            $this->transactionManager->rollback();
            $this->logger->error('An error occurred', ['exception' => $e]);
            throw ErrorException::new('Не удалось обновить запись');
        }
    }

    public function validateUpdateMeDto(UpdateMeDto $dto): void
    {
        if ($dto->isNoticesEnabled()) {
            if (!$dto->getNoticeChannels()) {
                throw ValidationException::new([
                    new ValidationError(
                        'notice_channels',
                        ValidationErrorSlugEnum::WrongField->getSlug(),
                        'Нужно выбрать хотя бы один способ уведомлений',
                    ),
                ]);
            }
        }

        if (count(array_unique($dto->getNoticeChannels())) !== count($dto->getNoticeChannels())) {
            throw ValidationException::new([
                new ValidationError(
                    'notice_channels',
                    ValidationErrorSlugEnum::WrongField->getSlug(),
                    'Способы уведомлений содержат повторяющиеся значения'
                ),
            ]);
        }
        if (count(array_unique($dto->getNoticeTypes())) !== count($dto->getNoticeTypes())) {
            throw ValidationException::new([
                new ValidationError(
                    'notice_types',
                    ValidationErrorSlugEnum::WrongField->getSlug(),
                    'Типы уведомлений содержат повторяющиеся значения'
                ),
            ]);
        }
    }

    public function changePassword(User $user, #[SensitiveParameter] string $newPassword, bool $needChangePassword = false): User
    {
        $user->setPassword($this->passwordHasherService->hashPassword($newPassword))
            ->setUpdatedAt(new DateTimeImmutable())
            ->setPasswordChangedAt(new DateTimeImmutable())
            ->setNeedChangePassword($needChangePassword);

        $updated = $this->userRepository->update($user);
        if (!$updated) {
            throw ErrorException::new('Не удалось обновить запись');
        }
        return $this->refreshCredentials($user);
    }

    public function delete(User $user, User $updater): User
    {
        if (!$updater->isAdmin() || !$updater->isActive()) {
            throw ErrorException::new('Вам запрещено выполнять это действие', 403);
        }
        if ($user->getId()->equals($updater->getId())) {
            throw ErrorException::new('Нельзя удалить себя', 400);
        }
        $user->setDeleted(true)
            ->setDeletedAt(new DateTimeImmutable());
        if ($this->userRepository->update($user)) {
            $this->statRefresher->refreshStats(force: true);
            return $user;
        }
        throw ErrorException::new('Не удалось удалить пользователя');
    }
}
