<?php

namespace App\Application\Dto\User;

use App\Application\Validator\Constraints as LAssert;
use App\Domain\Enum\RoleEnum;
use App\Domain\Enum\UserStatusEnum;
use Symfony\Component\Validator\Constraints as Assert;

readonly class UpdateUserDto
{
    /**
     * @param string[] $roles
     * @param value-of<UserStatusEnum> $status
     * @param bool $deleted
     */
    public function __construct(
        /** Роли */
        #[Assert\All([
            new Assert\Type('string'),
            new Assert\NotBlank(),
        ])]
        #[LAssert\EnumChoice(RoleEnum::class, true)]
        #[Assert\NotBlank]
        public array $roles,
        /** Статус */
        #[Assert\Type('string')]
        #[Assert\NotBlank]
        #[LAssert\EnumChoice(UserStatusEnum::class)]
        public string $status,
        /** Удален */
        #[Assert\Type('boolean')]
        public bool $deleted,
    ) {
    }
}
