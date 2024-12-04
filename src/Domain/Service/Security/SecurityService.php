<?php

namespace App\Domain\Service\Security;

use App\Domain\Dto\GeneratedToken;
use App\Domain\Helper\HString;
use DateTimeImmutable;

class SecurityService
{
    public function __construct(
        private int|null $accessTokenExpiresIn = null,
        private int|null $refreshTokenExpiresIn = null,
    ) {
        $this->accessTokenExpiresIn ??= 3600;
        $this->refreshTokenExpiresIn ??= 3600;
    }

    /**
     * Генерирует токен доступа.
     *
     * @return GeneratedToken
     */
    public function generateAccessToken(): GeneratedToken
    {
        return new GeneratedToken(
            HString::random(40),
            (new DateTimeImmutable())
                ->modify("+$this->accessTokenExpiresIn seconds"),
        );
    }

    /**
     * Генерирует токен обновления.
     *
     * @return GeneratedToken
     */
    public function generateRefreshToken(): GeneratedToken
    {
        return new GeneratedToken(
            HString::random(40),
            (new DateTimeImmutable())
                ->modify("+$this->refreshTokenExpiresIn seconds"),
        );
    }
}
