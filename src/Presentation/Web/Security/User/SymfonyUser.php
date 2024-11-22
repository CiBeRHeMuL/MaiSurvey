<?php

namespace App\Presentation\Web\Security\User;

use App\Domain\Entity\User;
use App\Domain\Enum\PermissionEnum;
use App\Domain\Enum\RoleEnum;
use Symfony\Component\Security\Core\User\UserInterface;

class SymfonyUser implements UserInterface
{
    public function __construct(
        private User $user,
    ) {
    }

    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @inheritDoc
     */
    public function getRoles(): array
    {
        $roles = $this->user->getRoles();
        return array_values(
            array_unique(
                array_merge(
                    ...array_map(
                        fn(RoleEnum $role) => array_map(fn(PermissionEnum $e) => $e->value, $role->getPermissions()),
                        $roles,
                    ),
                ),
            ),
        );
    }

    /**
     * @inheritDoc
     */
    public function eraseCredentials(): void
    {
    }

    /**
     * @inheritDoc
     */
    public function getUserIdentifier(): string
    {
        return $this->user->getId()->toRfc4122();
    }
}
