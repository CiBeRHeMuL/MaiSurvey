<?php

namespace App\Presentation\Web\Response\Model;

use App\Domain\Enum\RoleEnum;
use App\Presentation\Web\OpenApi\Attribute as LOA;

readonly class Role
{
    public function __construct(
        #[LOA\Enum(RoleEnum::class)]
        public string $slug,
        public string $name,
        public bool $import_enabled,
    ) {
    }

    public static function fromRole(RoleEnum $role): self
    {
        return new self(
            $role->getSlug(),
            $role->getName(),
            $role->importEnabled(),
        );
    }
}
