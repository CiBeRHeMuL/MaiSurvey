<?php

namespace App\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

//
//#[ORM\Entity]
//#[ORM\Table('user_session')]
class UserSession
{
    #[ORM\Id]
    private Uuid $id;
}
