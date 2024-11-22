<?php

namespace App\Domain\Dto\Auth;

use DateTimeImmutable;
use Symfony\Component\Uid\Uuid;

readonly class RefreshCredentialsDto
{
    public function __construct(
        private Uuid $userId,
        private string $token,
        private DateTimeImmutable $expiresAt,
    ) {
    }

    public function getUserId(): Uuid
    {
        return $this->userId;
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
