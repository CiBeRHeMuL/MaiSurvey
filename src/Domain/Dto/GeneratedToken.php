<?php

namespace App\Domain\Dto;

use DateTimeImmutable;

/**
 * Этот класс описывает созданный токен.
 */
readonly class GeneratedToken
{
    public function __construct(
        private string $token,
        private DateTimeImmutable $expiresAt,
    ) {
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getExpiresAt(): DateTimeImmutable
    {
        return $this->expiresAt;
    }
}
