<?php

namespace App\Domain\Service\Jwt;

interface JwtClaimsInterface
{
    /**
     * Create JwtClaimsInterface instance by decoded data
     *
     * @param array $decoded
     * @param string|null $salt
     *
     * @return JwtClaimsInterface
     */
    public static function createByDecoded(array $decoded, string|null $salt = null): JwtClaimsInterface;

    /**
     * Get all claims
     *
     * @return array
     */
    public function getClaims(): array;

    /**
     * Get salt
     *
     * @return string|null
     */
    public function getSalt(): string|null;
}
