<?php

namespace App\Domain\Repository;

use App\Domain\DataProvider\DataProviderInterface;
use App\Domain\Dto\Group\GetAllGroupsDto;
use App\Domain\Entity\Group;
use App\Domain\Repository\Common\RepositoryInterface;
use Iterator;
use Symfony\Component\Uid\Uuid;

interface GroupRepositoryInterface extends RepositoryInterface
{
    /**
     * @param GetAllGroupsDto $dto
     *
     * @return DataProviderInterface<Group>
     */
    public function findAll(GetAllGroupsDto $dto): DataProviderInterface;

    /**
     * Поиск по имени.
     *
     * @param string $name
     *
     * @return Group|null
     */
    public function findByName(string $name): Group|null;

    /**
     * Поиск по id
     *
     * @param Uuid $id
     *
     * @return Group|null
     */
    public function findById(Uuid $id): Group|null;

    /**
     * Список групп по списку называний
     *
     * @param string[] $groupNames
     *
     * @return Iterator<int, Group>
     */
    public function findByNames(array $groupNames): Iterator;
}
