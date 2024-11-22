<?php

namespace App\Presentation\Web\Service\Security;

use App\Domain\Entity\User;
use App\Domain\Service\Jwt\JwtServiceInterface;
use App\Domain\Service\Jwt\UserJwtClaims;
use App\Presentation\Web\Response\Model\UserCredentials;

class SecurityService
{
    public function __construct(
        private JwtServiceInterface $jwtService,
    ) {
    }

    public function getCredentialsForUser(User $user): UserCredentials
    {
        $accessTokenClaims = new UserJwtClaims(
            $user->getId(),
            $user->getAccessToken(),
            $user->getAccessTokenExpiresAt(),
        );
        $refreshTokenClaims = new UserJwtClaims(
            $user->getId(),
            $user->getRefreshToken(),
            $user->getRefreshTokenExpiresAt(),
        );

        return new UserCredentials(
            $this->jwtService->encode($accessTokenClaims),
            $this->jwtService->encode($refreshTokenClaims),
        );
    }
}
