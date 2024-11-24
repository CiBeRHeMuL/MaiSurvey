<?php

namespace App\Domain\Service\UserData;

use App\Domain\DataProvider\DataProviderInterface;
use App\Domain\Dto\GetAllUserDataDto;
use App\Domain\Entity\UserData;
use App\Domain\Enum\ValidationErrorSlugEnum;
use App\Domain\Exception\ValidationException;
use App\Domain\Repository\UserDataRepositoryInterface;
use App\Domain\Validation\ValidationError;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;

class UserDataService
{
    public const array GET_ALL_SORT = ['name'];

    private LoggerInterface $logger;

    public function __construct(
        private UserDataRepositoryInterface $userDataRepository,
        LoggerInterface $logger,
    ) {
        $this->setLogger($logger);
    }

    public function setLogger(LoggerInterface $logger): UserDataService
    {
        $this->logger = $logger;
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
     * @return DataProviderInterface
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
}
