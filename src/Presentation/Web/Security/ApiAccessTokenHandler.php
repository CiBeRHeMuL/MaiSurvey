<?php

namespace App\Presentation\Web\Security;

use App\Application\UseCase\User\GetUserUseCase;
use App\Domain\Service\Jwt\JwtServiceInterface;
use App\Domain\Service\Jwt\UserJwtClaims;
use App\Presentation\Web\Security\User\SymfonyUser;
use DateTimeImmutable;
use SensitiveParameter;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\AccessToken\AccessTokenHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

class ApiAccessTokenHandler implements AccessTokenHandlerInterface
{
    public function __construct(
        private JwtServiceInterface $jwtService,
        private GetUserUseCase $useCase,
    ) {
    }

    public function getUserBadgeFrom(#[SensitiveParameter] string $accessToken): UserBadge
    {
        $decoded = $this->jwtService->decode($accessToken, UserJwtClaims::class, 'user');
        if ($decoded === null || $decoded->getExpiredAt()->diff(new DateTimeImmutable())->invert === 0) {
            throw new AuthenticationException('Invalid credentials');
        }
        return new UserBadge(
            $decoded->getId()->toRfc4122(),
            /**
             * @param string $userIdentifier
             * @param array{decoded: UserJwtClaims} $attributes
             *
             * @return SymfonyUser|null
             */
            function (string $userIdentifier, array $attributes): SymfonyUser|null {
                $decoded = $attributes['decoded'];
                $user = $this->useCase
                    ->execute($decoded->getId());
                if (
                    $user !== null
                    && $user->getAccessToken() === $decoded->getToken()
                    && $user->getAccessTokenExpiresAt()->diff(new DateTimeImmutable())->invert === 1
                ) {
                    if ($user->isDeleted()) {
                        throw new AuthenticationException('Invalid credentials');
                    }
                    return new SymfonyUser($user);
                }
                return null;
            },
            compact('decoded'),
        );
    }
}
