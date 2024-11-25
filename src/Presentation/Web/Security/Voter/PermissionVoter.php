<?php

namespace App\Presentation\Web\Security\Voter;

use App\Domain\Enum\PermissionEnum;
use App\Presentation\Web\Security\User\SymfonyUser;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\CacheableVoterInterface;

class PermissionVoter implements CacheableVoterInterface
{
    public function supportsAttribute(string $attribute): bool
    {
        return PermissionEnum::tryFrom($attribute) !== null;
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
            if ($user instanceof SymfonyUser) {
                if (in_array(PermissionEnum::from($attribute)->value, $user->getRoles(), true)) {
                    return self::ACCESS_GRANTED;
                }
            }
        }
        return $result;
    }
}
