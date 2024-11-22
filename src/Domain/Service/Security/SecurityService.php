<?php

namespace App\Domain\Service\Security;

use App\Domain\Dto\GeneratedToken;
use App\Domain\Helper\HString;
use DateTimeImmutable;

class SecurityService
{
    /**
     * Генерирует токен доступа.
     *
     * @return GeneratedToken
     */
    public function generateAccessToken(): GeneratedToken
    {
        return new GeneratedToken(
            HString::random(40),
            (new DateTimeImmutable())->modify('+1 hour'),
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
            (new DateTimeImmutable())->modify('+1 month'),
        );
    }
}
