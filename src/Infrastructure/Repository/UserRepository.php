<?php

namespace App\Infrastructure\Repository;

use App\Domain\Entity\User;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\ValueObject\Email;
use App\Infrastructure\Repository\Common\AbstractRepository;
use Symfony\Component\Uid\Uuid;

class UserRepository extends AbstractRepository implements UserRepositoryInterface
{
    public function findById(Uuid $uuid): User|null
    {
        return $this
            ->getEntityManager()
            ->getRepository(User::class)
            ->find($uuid);
    }

    public function findByEmail(Email $email): User|null
    {
        return $this
            ->getEntityManager()
            ->getRepository(User::class)
            ->findOneBy(['email' => $email]);
    }
}
