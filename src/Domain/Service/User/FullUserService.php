<?php

namespace App\Domain\Service\User;

use App\Domain\Dto\Auth\SignUpStep1Dto;
use App\Domain\Dto\Auth\SignUpStep2Dto;
use App\Domain\Dto\User\CreateFullUserDto;
use App\Domain\Dto\UserData\CreateUserDataDto;
use App\Domain\Entity\User;
use App\Domain\Enum\ValidationErrorSlugEnum;
use App\Domain\Exception\ErrorException;
use App\Domain\Exception\ValidationException;
use App\Domain\Service\Auth\AuthService;
use App\Domain\Service\Db\TransactionManagerInterface;
use App\Domain\Service\Group\GroupService;
use App\Domain\Service\UserData\UserDataService;
use App\Domain\Validation\ValidationError;
use Psr\Log\LoggerInterface;
use Throwable;

class FullUserService
{
    private LoggerInterface $logger;

    public function __construct(
        LoggerInterface $logger,
        private AuthService $authService,
        private UserDataService $userDataService,
        private GroupService $groupService,
        private TransactionManagerInterface $transactionManager,
    ) {
    }

    public function setLogger(LoggerInterface $logger): FullUserService
    {
        $this->logger = $logger;
        $this->authService->setLogger($logger);
        $this->groupService->setLogger($logger);
        $this->userDataService->setLogger($logger);
        return $this;
    }

    public function createFullUser(CreateFullUserDto $dto): User
    {
        try {
            $this->transactionManager->beginTransaction();

            $user = $this
                ->authService
                ->signUpStep1(
                    new SignUpStep1Dto(
                        $dto->getEmail(),
                        $dto->getPassword(),
                        $dto->getPassword(),
                        $dto->getRole(),
                    ),
                );

            $group = null;
            if ($dto->getGroupId() !== null) {
                $group = $this
                    ->groupService
                    ->getById($dto->getGroupId());
                if ($group === null) {
                    throw ValidationException::new([
                        new ValidationError(
                            'group_id',
                            ValidationErrorSlugEnum::WrongField->getSlug(),
                            'Группа не найдена',
                        ),
                    ]);
                }
            }

            $userData = $this
                ->userDataService
                ->create(
                    new CreateUserDataDto(
                        $dto->getRole(),
                        $dto->getFirstName(),
                        $dto->getLastName(),
                        $dto->getPatronymic(),
                        $group,
                    ),
                );

            $user = $this
                ->authService
                ->signUpStep2(
                    $user,
                    new SignUpStep2Dto(
                        $userData->getId(),
                    ),
                );
            $this->transactionManager->commit();
            return $user;
        } catch (ErrorException|ValidationException $e) {
            $this->transactionManager->rollback();
            throw $e;
        } catch (Throwable $e) {
            $this->logger->error($e);
            $this->transactionManager->rollback();
            throw $e;
        }
    }
}
