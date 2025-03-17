<?php

namespace App\Presentation\Web\Response\Model;

use App\Domain\Entity\User as DomainUser;
use App\Domain\Enum\RoleEnum;
use OpenApi\Attributes as OA;
use App\Presentation\Web\OpenApi\Attribute as LOA;

readonly class LiteUser
{
    /**
     * @param string $id
     * @param string $email
     * @param UserData|null $data
     * @param string[] $roles
     */
    public function __construct(
        public string $id,
        #[OA\Property(format: 'email')]
        public string $email,
        public UserData|null $data,
        #[LOA\EnumItems(RoleEnum::class)]
        public array $roles,
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
        );
    }
}
