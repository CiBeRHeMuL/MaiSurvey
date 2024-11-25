<?php

namespace App\Domain\Repository;

use App\Domain\DataProvider\DataProviderInterface;
use App\Domain\Dto\UserData\GetAllUserDataDto;
use App\Domain\Entity\UserData;
use App\Domain\Repository\Common\RepositoryInterface;
use Symfony\Component\Uid\Uuid;

interface UserDataRepositoryInterface extends RepositoryInterface
{
    public function findById(Uuid $id): UserData|null;

    public function findByUserId(Uuid $userId): UserData|null;

    /**
     * @param GetAllUserDataDto $dto
     *
     * @return DataProviderInterface
     */
    public function findAll(GetAllUserDataDto $dto): DataProviderInterface;
}
