<?php

namespace App\Infrastructure\Repository;

use App\Domain\Entity\UserDataGroup;
use App\Domain\Repository\UserDataGroupRepositoryInterface;
use Qstart\Db\QueryBuilder\Query;
use Symfony\Component\Uid\Uuid;

class UserDataGroupRepository extends Common\AbstractRepository implements UserDataGroupRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function findByUserData(Uuid $userDataId): UserDataGroup|null
    {
        $q = Query::select()
            ->select(['*'])
            ->from($this->getClassTable(UserDataGroup::class))
            ->where(['user_data_id' => $userDataId->toRfc4122()]);
        return $this->findOneByQuery($q, UserDataGroup::class, ['group', 'userData']);
    }
}
