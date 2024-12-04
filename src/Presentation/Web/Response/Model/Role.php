<?php

namespace App\Presentation\Web\Response\Model;

use App\Domain\Enum\RoleEnum;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;

readonly class Role
{
    public function __construct(
        #[OA\Property(ref: new Model(type: RoleEnum::class))]
        public string $slug,
        public string $name,
        public bool $import_enabled,
    ) {
    }

    public static function fromRole(RoleEnum $role): self
    {
        return new self(
            $role->value,
            $role->getName(),
            $role->importEnabled(),
        );
    }
}
