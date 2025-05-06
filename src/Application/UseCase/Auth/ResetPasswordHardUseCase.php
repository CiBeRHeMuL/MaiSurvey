<?php

namespace App\Application\UseCase\Auth;

use App\Domain\Entity\User;
use App\Domain\Enum\ValidationErrorSlugEnum;
use App\Domain\Exception\ValidationException;
use App\Domain\Service\Auth\AuthService;
use App\Domain\Service\User\UserService;
use App\Domain\Validation\ValidationError;
use App\Domain\ValueObject\Email;
use Psr\Log\LoggerInterface;

class ResetPasswordHardUseCase
{
    private LoggerInterface $logger;

    public function __construct(
        private AuthService $authService,
        private UserService $userService,
        LoggerInterface $logger,
    ) {
        $this->setLogger($logger);
    }

    public function setLogger(LoggerInterface $logger): ResetPasswordHardUseCase
    {
        $this->logger = $logger;
        $this->userService->setLogger($logger);
        $this->authService->setLogger($logger);
        return $this;
    }

    /**
     * Сбросить пароль принудительно без подтверждения
     *
     * @param Email $email
     * @param string $newPassword
     *
     * @return User
     */
    public function execute(Email $email, string $newPassword): User
    {
        $user = $this->userService->getByEmail($email);
        if (!$user) {
            throw ValidationException::new([
                new ValidationError(
                    'email',
                    ValidationErrorSlugEnum::NotFound->getSlug(),
                    'Пользователь не найден',
                ),
            ]);
        }

        return $this->authService->resetPasswordHard($user, $newPassword);
    }
}
