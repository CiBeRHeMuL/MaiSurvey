<?php

namespace App\Domain\Service\Jwt;

interface JwtServiceInterface
{
    public function encode(JwtClaimsInterface $jwt): string;

    /**
     * @template T of JwtClaimsInterface
     * @param string $token
     * @param class-string<T> $jwtClassName
     * @param string|null $salt
     *
     * @return T&JwtClaimsInterface|null
     */
    public function decode(string $token, string $jwtClassName, string|null $salt = null): JwtClaimsInterface|null;
}
