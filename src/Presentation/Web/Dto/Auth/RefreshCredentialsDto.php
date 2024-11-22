<?php

namespace App\Presentation\Web\Dto\Auth;

readonly class RefreshCredentialsDto
{
    public function __construct(
        public string $refresh_token,
    ) {
    }
}
