<?php

namespace App\Application\Dto\User;

use App\Application\OpenApi\Attribute as LOA;
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
     * @param bool $need_change_password
     */
    public function __construct(
        /** Роли */
        #[LOA\EnumItems(RoleEnum::class)]
        #[Assert\All([
            new Assert\Type('string'),
            new Assert\NotBlank(),
        ])]
        #[LAssert\EnumChoice(RoleEnum::class, true)]
        #[Assert\NotBlank]
        public array $roles,
        /** Статус */
        #[LOA\Enum(UserStatusEnum::class)]
        #[Assert\Type('string')]
        #[Assert\NotBlank]
        #[LAssert\EnumChoice(UserStatusEnum::class)]
        public string $status,
        /** Удален */
        #[Assert\Type('boolean')]
        public bool $deleted,
        /** Необходимо сменить пароль */
        #[Assert\Type('boolean')]
        public bool $need_change_password,
    ) {
    }
}
