<?php

namespace App\Domain\Service\Auth;

use App\Domain\Dto\Auth\RefreshCredentialsDto;
use App\Domain\Dto\Auth\SignInDto;
use App\Domain\Dto\Auth\SignUpStep1Dto;
use App\Domain\Dto\Auth\SignUpStep2Dto;
use App\Domain\Dto\User\CreateUserDto;
use App\Domain\Entity\User;
use App\Domain\Enum\UserStatusEnum;
use App\Domain\Enum\ValidationErrorSlugEnum;
use App\Domain\Exception\ErrorException;
use App\Domain\Exception\ValidationException;
use App\Domain\Service\Security\PasswordCheckerServiceInterface;
use App\Domain\Service\Security\PasswordVerificationServiceInterface;
use App\Domain\Service\User\UserService;
use App\Domain\Service\UserData\UserDataService;
use App\Domain\Validation\ValidationError;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;

class AuthService
{
    private LoggerInterface $logger;

    public function __construct(
        LoggerInterface $logger,
        private UserService $userService,
        private PasswordVerificationServiceInterface $passwordVerificationService,
        private UserDataService $userDataService,
        private PasswordCheckerServiceInterface $passwordCheckerService,
    ) {
        $this->setLogger($logger);
    }

    public function setLogger(LoggerInterface $logger): AuthService
    {
        $this->logger = $logger;
        $this->userService->setLogger($this->logger);
        return $this;
    }

    /** Вход в приложение */
    public function signIn(SignInDto $dto): User
    {
        $user = $this
            ->userService
            ->getByEmail($dto->getEmail());
        if ($user === null) {
            throw ValidationException::new(
                [
                    new ValidationError(
                        'email',
                        ValidationErrorSlugEnum::WrongField->getSlug(),
                        'Некорректная почта или пароль',
                    ),
                ],
            );
        } else {
            if ($user->isDraft()) {
                throw ErrorException::new(
                    'Для входа необходимо завершить регистрацию',
                    400,
                );
            }
            if ($user->isDeleted()) {
                throw ErrorException::new(
                    'Пользователь удален',
                    400,
                );
            }
            if ($user->isActive() === false) {
                throw ErrorException::new(
                    'Неизвестная ошибка, обратитесь в поддержку',
                );
            }
            if (
                $this
                    ->passwordVerificationService
                    ->verifyPassword($dto->getPassword(), $user->getPassword())
            ) {
                $this
                    ->userService
                    ->refreshCredentials($user);
                return $user;
            } else {
                throw ValidationException::new(
                    [
                        new ValidationError(
                            'email',
                            ValidationErrorSlugEnum::WrongField->getSlug(),
                            'Некорректная почта или пароль',
                        ),
                    ],
                );
            }
        }
    }

    /** Регистрация, первый шаг (почта + пароль) */
    public function signUpStep1(SignUpStep1Dto $dto): User
    {
        $existingUser = $this
            ->userService
            ->getByEmail($dto->getEmail());
        if ($existingUser === null) {
            if (
                $dto->getPassword() !== $dto->getRepeatPassword()
            ) {
                throw ValidationException::new([
                    new ValidationError(
                        'password',
                        ValidationErrorSlugEnum::WrongField->getSlug(),
                        'Пароли не совпадают',
                    ),
                    new ValidationError(
                        'repeat_password',
                        ValidationErrorSlugEnum::WrongField->getSlug(),
                        'Пароли не совпадают',
                    ),
                ]);
            }
            $this->passwordCheckerService->checkPasswordStrength($dto->getPassword());
            return $this
                ->userService
                ->create(
                    new CreateUserDto(
                        $dto->getEmail(),
                        UserStatusEnum::Draft,
                        $dto->getRole(),
                        $dto->getPassword(),
                    ),
                    false,
                );
        } elseif ($existingUser->isDraft()) {
            return $existingUser;
        } else {
            throw ValidationException::new([
                new ValidationError(
                    'email',
                    ValidationErrorSlugEnum::WrongField->getSlug(),
                    'Пользователь уже существует',
                ),
            ]);
        }
    }

    /** Регистрация, второй шаг (персональные данные) */
    public function signUpStep2(User $user, SignUpStep2Dto $dto): User
    {
        if ($user->isDraft()) {
            if ($user->getData() !== null) {
                throw ValidationException::new([
                    new ValidationError(
                        'user_data_id',
                        ValidationErrorSlugEnum::WrongField->getSlug(),
                        'К пользователю уже привязаны другие данные',
                    ),
                ]);
            } else {
                $userData = $this
                    ->userDataService
                    ->getById($dto->getUserDataId());
                if ($userData === null) {
                    throw ValidationException::new([
                        new ValidationError(
                            'user_data_id',
                            ValidationErrorSlugEnum::WrongField->getSlug(),
                            'Данные пользователя не найдены, обратитесь в поддержку',
                        ),
                    ]);
                } elseif ($userData->getUserId() !== null) {
                    throw ValidationException::new([
                        new ValidationError(
                            'user_data_id',
                            ValidationErrorSlugEnum::WrongField->getSlug(),
                            'Данные пользователя уже привязаны к другому пользователю',
                        ),
                    ]);
                } elseif (!in_array($userData->getForRole(), $user->getRoles(), true)) {
                    throw ValidationException::new([
                        new ValidationError(
                            'user_data_id',
                            ValidationErrorSlugEnum::WrongField->getSlug(),
                            'Невозможно привязать данные к пользователю',
                        ),
                    ]);
                } else {
                    $user
                        ->setData($userData)
                        ->setStatus(UserStatusEnum::Active);
                    $userData
                        ->setUser($user)
                        ->setUserId($user->getId());
                    if (
                        $this
                            ->userService
                            ->update($user)
                    ) {
                        if (
                            $this
                                ->userDataService
                                ->update($userData)
                        ) {
                            return $user;
                        }
                    }
                    throw ErrorException::new(
                        'Не удалось сохранить пользователя, обратитесь в поддержку',
                    );
                }
            }
        } else {
            throw ValidationException::new([
                new ValidationError(
                    'user_data_id',
                    ValidationErrorSlugEnum::WrongField->getSlug(),
                    'Пользователь не существует или уже зарегистрирован',
                ),
            ]);
        }
    }

    /**
     * Обновить токены доступа.
     *
     * @param RefreshCredentialsDto $dto
     *
     * @return User
     * @throws ErrorException
     */
    public function refreshCredentials(RefreshCredentialsDto $dto): User
    {
        $user = $this
            ->userService
            ->getById($dto->getUserId());
        if (
            $user === null
            || $user->isDeleted()
            || $user->getRefreshToken() !== $dto->getToken()
            || $user->getRefreshTokenExpiresAt()->diff(new DateTimeImmutable())->invert === 0
        ) {
            throw ErrorException::new(
                'Некорректный токен',
                401,
            );
        }

        return $this
            ->userService
            ->refreshCredentials($user);
    }
}
