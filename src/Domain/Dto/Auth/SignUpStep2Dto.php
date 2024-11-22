<?php

namespace App\Domain\Dto\Auth;

use Symfony\Component\Uid\Uuid;

readonly class SignUpStep2Dto
{
    public function __construct(
        private Uuid $userDataId,
    ) {
    }

    public function getUserDataId(): Uuid
    {
        return $this->userDataId;
    }
}
