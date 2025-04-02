<?php

namespace App\Presentation\Web\Security\Voter;

use App\Domain\Enum\UserStatusEnum;
use App\Presentation\Web\Security\User\SymfonyUser;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\CacheableVoterInterface;

class StatusVoter implements CacheableVoterInterface
{
    public function __construct(
        private LoggerInterface $logger,
    )
    {
    }

    public function supportsAttribute(string $attribute): bool
    {
        return UserStatusEnum::tryFrom($attribute) !== null;
    }

    /**
     * @inheritDoc
     */
    public function supportsType(string $subjectType): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function vote(TokenInterface $token, mixed $subject, array $attributes): int
    {
        $result = self::ACCESS_ABSTAIN;
        foreach ($attributes as $attribute) {
            if (!is_string($attribute) || !$this->supportsAttribute($attribute)) {
                continue;
            }

            $result = self::ACCESS_DENIED;
            $user = $token->getUser();
            $this->logger->error(json_encode($user));
            if ($user instanceof SymfonyUser) {
                $this->logger->error(json_encode($user->getUser()));
                if (
                    $user->getUser()->isDeleted() === false
                    && $user->getUser()->getStatus()->value === $attribute
                ) {
                    return self::ACCESS_GRANTED;
                }
            }
        }
        return $result;
    }
}
