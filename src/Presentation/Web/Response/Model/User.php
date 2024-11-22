<?php

namespace App\Presentation\Web\Response\Model;

use App\Domain\Entity\User as DomainUser;
use App\Domain\Enum\PermissionEnum;
use App\Domain\Enum\RoleEnum;
use App\Domain\Enum\UserStatusEnum;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;

readonly class User
{
    /**
     * @param string $id
     * @param string $email
     * @param UserData|null $data
     * @param string $status
     * @param bool $deleted
     * @param string|null $deleted_at
     * @param string[] $permissions
     */
    public function __construct(
        public string $id,
        #[OA\Property(format: 'email')]
        public string $email,
        public UserData|null $data,
        #[OA\Property(ref: new Model(type: UserStatusEnum::class))]
        public string $status,
        public bool $deleted,
        #[OA\Property(format: 'date-time')]
        public string|null $deleted_at,
        #[OA\Property(
            type: 'array',
            items: new OA\Items(ref: new Model(type: PermissionEnum::class)),
        )]
        public array $permissions,
    ) {
    }

    public static function fromUser(DomainUser $user): self
    {
        $roles = $user->getRoles();
        return new self(
            $user->getId()->toRfc4122(),
            $user->getEmail()->getEmail(),
            $user->getData() !== null
                ? UserData::fromData($user->getData())
                : null,
            $user->getStatus()->value,
            $user->isDeleted(),
            $user->getDeletedAt()?->format(DATE_RFC3339),
            array_values(
                array_unique(
                    array_merge(
                        ...array_map(
                            fn(RoleEnum $role) => array_map(fn(PermissionEnum $e) => $e->value, $role->getPermissions()),
                            $roles,
                        ),
                    ),
                ),
            ),
        );
    }
}
