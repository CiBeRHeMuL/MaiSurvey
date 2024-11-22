<?php

namespace App\Domain\Repository;

use App\Domain\Entity\User;
use App\Domain\Repository\Common\RepositoryInterface;
use App\Domain\ValueObject\Email;
use Symfony\Component\Uid\Uuid;

interface UserRepositoryInterface extends RepositoryInterface
{
    public function findById(Uuid $uuid): User|null;

    public function findByEmail(Email $email): User|null;
}
