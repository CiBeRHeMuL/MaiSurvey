<?php

namespace App\Domain\Repository;

use App\Domain\DataProvider\DataProviderInterface;
use App\Domain\Dto\Group\GetAllGroupsDto;
use App\Domain\Entity\Group;
use App\Domain\Repository\Common\RepositoryInterface;

interface GroupRepositoryInterface extends RepositoryInterface
{
    /**
     * @param GetAllGroupsDto $dto
     *
     * @return DataProviderInterface
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
}
