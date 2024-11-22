<?php

namespace App\Presentation\Web\Dto\Auth;

readonly class RefreshCredentialsDto
{
    public function __construct(
        /** Токен для обновления */
        public string $refresh_token,
    ) {
    }
}
