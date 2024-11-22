<?php

namespace App\Domain\Service\User;

use App\Domain\Dto\CreateUserDto;
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
        return $this->userRepository->update($user);
    }
}
