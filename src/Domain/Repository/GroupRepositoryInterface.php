<?php

namespace App\Domain\Repository;

use App\Domain\DataProvider\DataProviderInterface;
use App\Domain\Dto\GetAllGroupsDto;
use App\Domain\Dto\GetAllUserDataDto;
use App\Domain\Entity\UserData;
use App\Domain\Repository\Common\RepositoryInterface;
use Symfony\Component\Uid\Uuid;

interface GroupRepositoryInterface extends RepositoryInterface
{
    /**
     * @param GetAllGroupsDto $dto
     *
     * @return DataProviderInterface
     */
    public function findAll(GetAllGroupsDto $dto): DataProviderInterface;
}
