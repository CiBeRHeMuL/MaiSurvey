<?php

namespace App\Presentation\Web\Response\Model;

use App\Domain\Entity\UserDataGroup as DomainUserDataGroup;
use OpenApi\Attributes as OA;

readonly class UserDataGroup
{
    public function __construct(
        #[OA\Property(format: 'uuid')]
        public string $id,
    ) {
    }

    public static function fromUserDataGroup(DomainUserDataGroup $group): self
    {
        return new self(
            $group->getGroupId()->toRfc4122(),
        );
    }
}
