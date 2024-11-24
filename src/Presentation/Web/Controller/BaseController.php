<?php

namespace App\Presentation\Web\Controller;

use App\Presentation\Web\Security\User\SymfonyUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\User\UserInterface;

abstract class BaseController extends AbstractController
{
    protected function getUser(): ?SymfonyUser
    {
        return parent::getUser();
    }
}
