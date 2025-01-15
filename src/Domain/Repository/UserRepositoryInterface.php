<?php

namespace App\Domain\Repository;

use App\Domain\DataProvider\DataProviderInterface;
use App\Domain\DataProvider\DataSortInterface;
use App\Domain\Dto\User\GetAllUsersDto;
use App\Domain\Entity\User;
use App\Domain\Repository\Common\RepositoryInterface;
use App\Domain\ValueObject\Email;
use Symfony\Component\Uid\Uuid;

interface UserRepositoryInterface extends RepositoryInterface
{
    public function findById(Uuid $uuid): User|null;

    public function findByEmail(Email $email): User|null;

    /**
     * Список пользователей с пагинацией и сортировкой.
     *
     * @param GetAllUsersDto $dto
     *
     * @return DataProviderInterface<User>
     */
    public function findAll(GetAllUsersDto $dto): DataProviderInterface;

    /**
     * Поиск по почтам
     *
     * @param Email[] $emails
     *
     * @return User[]
     */
    public function findAllByEmails(array $emails): array;

    /**
     * @param int $count
     * @param DataSortInterface $sort
     *
     * @return DataProviderInterface<User>
     */
    public function findLastN(int $count, DataSortInterface $sort): DataProviderInterface;
}
