<?php

namespace App\Domain\Service\User;

use App\Domain\DataProvider\DataProviderInterface;
use App\Domain\Dto\User\CreateUserDto;
use App\Domain\Dto\User\GetAllUsersDto;
use App\Domain\Entity\User;
use App\Domain\Enum\ValidationErrorSlugEnum;
use App\Domain\Exception\ErrorException;
use App\Domain\Exception\ValidationException;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\Service\Security\EmailCheckerServiceInterface;
use App\Domain\Service\Security\PasswordHasherServiceInterface;
use App\Domain\Service\Security\SecurityService;
use App\Domain\Validation\ValidationError;
use App\Domain\ValueObject\Email;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;

class UserService
{
    public const array GET_ALL_SORT = ['name', 'created_at', 'deleted', 'email'];

    private LoggerInterface $logger;

    public function __construct(
        private UserRepositoryInterface $userRepository,
        private EmailCheckerServiceInterface $emailCheckerService,
        private SecurityService $securityService,
        private PasswordHasherServiceInterface $passwordHasherService,
        LoggerInterface $logger,
    ) {
        $this->setLogger($logger);
    }

    public function setLogger(LoggerInterface $logger): UserService
    {
        $this->logger = $logger;
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

        $accessToken = $this->securityService->generateAccessToken();
        $refreshToken = $this->securityService->generateRefreshToken();

        $user = new User();
        $user
            ->setEmail($dto->getEmail())
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
            ->setDeleted(false);

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

    public function update(User $user): bool
    {
        $user
            ->setUpdatedAt(new DateTimeImmutable());
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
                    'name',
                    ValidationErrorSlugEnum::WrongField->getSlug(),
                    sprintf('Сортировка доступна по полям: %s', implode(', ', self::GET_ALL_SORT)),
                ),
            ]);
        }

        if (
            $dto->getCreatedFrom() !== null
            && $dto->getCreatedTo() !== null
            && $dto->getCreatedTo()->diff($dto->getCreatedFrom())->invert === 1
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
}
