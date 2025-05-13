<?php

namespace App\Presentation\Web\Response\Model;

use App\Application\Aggregate\Me as DomainMe;
use App\Domain\Enum\NoticeChannelEnum;
use App\Domain\Enum\NoticeTypeEnum;
use App\Domain\Enum\PermissionEnum;
use App\Domain\Enum\RoleEnum;
use App\Domain\Enum\UserStatusEnum;
use App\Presentation\Web\OpenApi\Attribute as LOA;
use OpenApi\Attributes as OA;

readonly class Me
{
    /**
     * @param string $id
     * @param string $email
     * @param UserData|null $data
     * @param string $status
     * @param bool $deleted
     * @param string|null $deleted_at
     * @param string[] $permissions
     * @param string[] $roles
     * @param bool $need_change_password
     * @param string|null $password_changed_at
     * @param bool $notices_enabled
     * @param array<value-of<NoticeTypeEnum>, int> $notice_types
     * @param array<value-of<NoticeChannelEnum>, int> $notice_channels
     * @param bool $telegram_connected
     * @param bool $can_connect_telegram
     * @param string|null $telegram_connect_link
     */
    public function __construct(
        public string $id,
        #[OA\Property(format: 'email')]
        public string $email,
        public UserData|null $data,
        #[LOA\Enum(UserStatusEnum::class)]
        public string $status,
        public bool $deleted,
        #[OA\Property(format: 'date-time')]
        public string|null $deleted_at,
        #[LOA\EnumItems(PermissionEnum::class)]
        public array $permissions,
        #[LOA\EnumItems(RoleEnum::class)]
        public array $roles,
        public bool $need_change_password,
        #[OA\Property(format: 'date-time')]
        public string|null $password_changed_at,
        public bool $notices_enabled,
        #[LOA\EnumItems(NoticeTypeEnum::class)]
        public array $notice_types,
        #[LOA\EnumItems(NoticeChannelEnum::class)]
        public array $notice_channels,
        public bool $telegram_connected,
        public bool $can_connect_telegram,
        public string|null $telegram_connect_link,
    ) {
    }

    public static function fromMe(DomainMe $me): self
    {
        $user = $me->getUser();
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
            array_values(array_map(fn(RoleEnum $r) => $r->value, $user->getRoles())),
            $user->isNeedChangePassword(),
            $user->getPasswordChangedAt()?->format(DATE_RFC3339),
            $user->isNoticesEnabled(),
            array_map(static fn(NoticeTypeEnum $t) => $t->value, $user->getNoticeTypes()),
            array_map(static fn(NoticeChannelEnum $t) => $t->value, $user->getNoticeChannels()),
            $user->isTelegramConnected(),
            $user->canConnectTelegram(),
            $me->getTelegramConnectLink(),
        );
    }
}
