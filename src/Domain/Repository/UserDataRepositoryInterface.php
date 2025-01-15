<?php

namespace App\Domain\Repository;

use App\Domain\DataProvider\DataProviderInterface;
use App\Domain\DataProvider\DataSortInterface;
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
     * Найти последние N записей с учетом сортировки
     *
     * @param int $count
     * @param DataSortInterface $sort
     *
     * @return DataProviderInterface
     */
    public function findLastN(int $count, DataSortInterface $sort): DataProviderInterface;

    /**
     * @param string[] $names
     *
     * @return string[]
     */
    public function findEmailsByNames(array $names): array;
}
