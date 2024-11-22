<?php

namespace App\Presentation\Web\Response\Model;

readonly class SigningInUser
{
    public function __construct(
        public UserCredentials $credentials,
        public User $user,
    ) {
    }
}
