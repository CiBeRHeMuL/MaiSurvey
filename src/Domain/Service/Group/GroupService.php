<?php

namespace App\Domain\Service\Group;

use App\Domain\DataProvider\DataProviderInterface;
use App\Domain\Dto\Group\CreateGroupDto;
use App\Domain\Dto\Group\GetAllGroupsDto;
use App\Domain\Entity\Group;
use App\Domain\Enum\ValidationErrorSlugEnum;
use App\Domain\Exception\ErrorException;
use App\Domain\Exception\ValidationException;
use App\Domain\Repository\GroupRepositoryInterface;
use App\Domain\Validation\ValidationError;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;

class GroupService
{
    public const array GET_ALL_SORT = ['name'];

    private LoggerInterface $logger;

    public function __construct(
        private GroupRepositoryInterface $groupRepository,
        LoggerInterface $logger,
    ) {
        $this->setLogger($logger);
    }

    public function setLogger(LoggerInterface $logger): GroupService
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * Получить список групп с учетом фильтров и пагинации.
     *
     * @param GetAllGroupsDto $dto
     *
     * @return DataProviderInterface
     */
    public function getAll(GetAllGroupsDto $dto): DataProviderInterface
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

        return $this->groupRepository->findAll($dto);
    }

    public function getByName(string $name): Group|null
    {
        return $this
            ->groupRepository
            ->findByName($name);
    }

    public function create(CreateGroupDto $dto): Group
    {
        $existing = $this
            ->groupRepository
            ->findByName($dto->getName());
        if ($existing !== null) {
            throw ValidationException::new([
                new ValidationError(
                    'name',
                    ValidationErrorSlugEnum::AlreadyExists->getSlug(),
                    'Группа уже существует',
                ),
            ]);
        }

        $group = new Group();
        $group
            ->setName($dto->getName())
            ->setCreatedAt(new DateTimeImmutable())
            ->setUpdatedAt(new DateTimeImmutable());
        if ($this->groupRepository->create($group) === false) {
            throw ErrorException::new(
                'Не удалось сохранить группу',
                400,
            );
        }
        return $group;
    }

    public function getById(Uuid $id): Group|null
    {
        return $this
            ->groupRepository
            ->findById($id);
    }
}
