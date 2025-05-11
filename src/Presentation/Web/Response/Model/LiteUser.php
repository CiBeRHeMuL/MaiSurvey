<?php

namespace App\Presentation\Web\Response\Model;

use App\Domain\Entity\User as DomainUser;
use App\Domain\Enum\RoleEnum;
use App\Domain\Enum\UserStatusEnum;
use App\Presentation\Web\OpenApi\Attribute as LOA;
use OpenApi\Attributes as OA;

readonly class LiteUser
{
    /**
     * @param string $id
     * @param string $email
     * @param UserData|null $data
     * @param string[] $roles
     * @param string $status
     */
    public function __construct(
        public string $id,
        #[OA\Property(format: 'email')]
        public string $email,
        public UserData|null $data,
        #[LOA\EnumItems(RoleEnum::class)]
        public array $roles,
        #[LOA\Enum(UserStatusEnum::class)]
        public string $status,
    ) {
    }

    public static function fromUser(DomainUser $user): self
    {
        return new self(
            $user->getId()->toRfc4122(),
            $user->getEmail()->getEmail(),
            $user->getData() !== null
                ? UserData::fromData($user->getData())
                : null,
            array_values(array_map(fn(RoleEnum $r) => $r->value, $user->getRoles())),
            $user->getStatus()->value,
        );
    }
}
