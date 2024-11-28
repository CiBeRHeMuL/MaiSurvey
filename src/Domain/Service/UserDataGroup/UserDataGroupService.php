<?php

namespace App\Domain\Service\UserDataGroup;

use App\Domain\Dto\UserDataGroup\CreateUserDataGroupDto;
use App\Domain\Entity\UserDataGroup;
use App\Domain\Enum\ValidationErrorSlugEnum;
use App\Domain\Exception\ErrorException;
use App\Domain\Exception\ValidationException;
use App\Domain\Repository\UserDataGroupRepositoryInterface;
use App\Domain\Service\Db\TransactionManagerInterface;
use App\Domain\Validation\ValidationError;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;
use Throwable;

class UserDataGroupService
{
    private LoggerInterface $logger;

    public function __construct(
        LoggerInterface $logger,
        private UserDataGroupRepositoryInterface $userDataGroupRepository,
        private TransactionManagerInterface $transactionManager,
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
        $this->validateCreateDto($dto);

        $group = $this->entityFromDto($dto);
        if ($this->userDataGroupRepository->create($group) === false) {
            throw ErrorException::new(
                'Не удалось сохранить группу',
                400,
            );
        }
        return $group;
    }

    /**
     * Массовое создание данных
     *
     * @param CreateUserDataGroupDto[] $dtos
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
            $entities = [];
            foreach ($dtos as $dto) {
                if ($validate) {
                    $this->validateCreateDto($dto);
                }

                $entity = $this->entityFromDto($dto);
                $entities[] = $entity;
            }

            $created = $this->userDataGroupRepository->createMulti($entities);

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

    private function entityFromDto(CreateUserDataGroupDto $dto): UserDataGroup
    {
        $group = new UserDataGroup();
        $group
            ->setUserDataId($dto->getUserData()->getId())
            ->setGroupId($dto->getGroup()->getId())
            ->setUserData($dto->getUserData())
            ->setGroup($dto->getGroup())
            ->setCreatedAt(new DateTimeImmutable())
            ->setUpdatedAt(new DateTimeImmutable());
        return $group;
    }

    private function validateCreateDto(CreateUserDataGroupDto $dto): void
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
    }
}
