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
     * @return DataProviderInterface<UserData>
     */
    public function findAll(GetAllUserDataDto $dto): DataProviderInterface;

    /**
     * @param string[] $names
     *
     * @return string[]
     */
    public function findEmailsByNames(array $names): array;

    /**
     * @param string[] $ids
     *
     * @return UserData[]
     */
    public function findAllByIdsWithIdsOrder(array $ids): array;
}
