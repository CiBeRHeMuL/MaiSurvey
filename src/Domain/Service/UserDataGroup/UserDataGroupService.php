<?php

namespace App\Domain\Service\UserDataGroup;

use App\Domain\Dto\UserDataGroup\CreateUserDataGroupDto;
use App\Domain\Entity\UserDataGroup;
use App\Domain\Enum\ValidationErrorSlugEnum;
use App\Domain\Exception\ErrorException;
use App\Domain\Exception\ValidationException;
use App\Domain\Repository\UserDataGroupRepositoryInterface;
use App\Domain\Validation\ValidationError;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;

class UserDataGroupService
{
    private LoggerInterface $logger;

    public function __construct(
        LoggerInterface $logger,
        private UserDataGroupRepositoryInterface $userDataGroupRepository,
    ) {
        $this->setLogger($logger);
    }

    public function setLogger(LoggerInterface $logger): UserDataGroupService
    {
        $this->logger = $logger;
        return $this;
    }

    public function create(CreateUserDataGroupDto $dto): UserDataGroup
    {
        $existingGroup = $this
            ->userDataGroupRepository
            ->findByUserData($dto->getUserData()->getId());
        if ($existingGroup) {
            throw ValidationException::new([
                new ValidationError(
                    'user_data_id',
                    ValidationErrorSlugEnum::AlreadyExists->getSlug(),
                    'К пользователю уже привязана другая группа',
                ),
            ]);
        }
        $group = new UserDataGroup();
        $group
            ->setUserDataId($dto->getUserData()->getId())
            ->setGroupId($dto->getGroup()->getId())
            ->setUserData($dto->getUserData())
            ->setGroup($dto->getGroup())
            ->setCreatedAt(new DateTimeImmutable())
            ->setUpdatedAt(new DateTimeImmutable());
        if ($this->userDataGroupRepository->create($group) === false) {
            throw ErrorException::new(
                'Не удалось сохранить группу',
                400,
            );
        }
        return $group;
    }
}
