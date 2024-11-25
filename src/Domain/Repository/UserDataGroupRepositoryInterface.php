<?php

namespace App\Domain\Repository;

use App\Domain\Entity\UserDataGroup;
use App\Domain\Repository\Common\RepositoryInterface;
use Symfony\Component\Uid\Uuid;

interface UserDataGroupRepositoryInterface extends RepositoryInterface
{
    /**
     * Поиск по id пользовательских данных
     *
     * @param Uuid $userDataId
     *
     * @return UserDataGroup|null
     */
    public function findByUserData(Uuid $userDataId): UserDataGroup|null;
}
