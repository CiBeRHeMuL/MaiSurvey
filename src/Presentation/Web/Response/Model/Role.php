<?php

namespace App\Presentation\Web\Response\Model;

use App\Domain\Enum\RoleEnum;
use App\Presentation\Web\OpenApi\Attribute as LOA;

readonly class Role
{
    /**
     * @param string $slug
     * @param string $name
     * @param bool $import_enabled
     * @param string[] $available_additional_roles
     * @param bool $main
     */
    public function __construct(
        #[LOA\Enum(RoleEnum::class)]
        public string $slug,
        public string $name,
        public bool $import_enabled,
        #[LOA\EnumItems(RoleEnum::class)]
        public array $available_additional_roles,
        public bool $main,
    ) {
    }

    public static function fromRole(RoleEnum $role): self
    {
        return new self(
            $role->getSlug(),
            $role->getName(),
            $role->importEnabled(),
            array_map(
                fn(RoleEnum $e) => $e->getSlug(),
                $role->getAvailableAdditionalRoles(),
            ),
            $role->isMain(),
        );
    }
}
