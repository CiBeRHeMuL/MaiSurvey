<?php

namespace App\Domain\Service\Security;

use App\Domain\Dto\GeneratedToken;
use App\Domain\Helper\HString;
use DateTimeImmutable;

class SecurityService
{
    public function __construct(
        private bool $infinityTokens,
    ) {
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
                ->modify(
                    $this->infinityTokens
                        ? '+10 years'
                        : '+1 hour',
                ),
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
                ->modify(
                    $this->infinityTokens
                        ? '+10 years'
                        : '+1 hour',
                ),
        );
    }
}
