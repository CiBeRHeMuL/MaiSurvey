<?php

namespace App\Domain\Service\Jwt;

use DateTimeImmutable;
use Symfony\Component\Uid\Uuid;

class UserJwtClaims implements JwtClaimsInterface
{
    public function __construct(
        protected Uuid $id,
        protected string $token,
        protected DateTimeImmutable $expiredAt,
    ) {
    }

    public static function createByDecoded(array $decoded, string|null $salt = null): JwtClaimsInterface
    {
        return new self(
            new Uuid($decoded['id']),
            $decoded['token'],
            new DateTimeImmutable($decoded['expiredAt']),
        );
    }

    public function getClaims(): array
    {
        return [
            'id' => $this->id,
            'token' => $this->token,
            'expiredAt' => $this->expiredAt->format(DATE_RFC3339),
        ];
    }

    public function getSalt(): string|null
    {
        return 'user';
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getExpiredAt(): DateTimeImmutable
    {
        return $this->expiredAt;
    }
}
