<?php

namespace App\Presentation\Web\Response\Model;

use App\Domain\Entity\User as DomainUser;
use OpenApi\Attributes as OA;

readonly class LiteUser
{
    /**
     * @param string $id
     * @param string $email
     * @param UserData|null $data
     */
    public function __construct(
        public string $id,
        #[OA\Property(format: 'email')]
        public string $email,
        public UserData|null $data,
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
        );
    }
}
