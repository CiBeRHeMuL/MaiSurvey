<?php

namespace App\Infrastructure\Service\Security;

use App\Domain\Service\Security\EmailCheckerServiceInterface;
use App\Domain\ValueObject\Email;

class EmailCheckerService implements EmailCheckerServiceInterface
{
    /**
     * @inheritDoc
     */
    public function checkEmail(Email $email): void
    {
    }
}
