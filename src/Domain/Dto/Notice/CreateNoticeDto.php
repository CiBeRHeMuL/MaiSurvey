<?php

namespace App\Domain\Dto\Notice;

use App\Domain\Entity\User;
use App\Domain\Enum\NoticeChannelEnum;
use App\Domain\Enum\NoticeTypeEnum;

readonly class CreateNoticeDto
{
    public function __construct(
        private User $user,
        private NoticeChannelEnum $channel,
        private NoticeTypeEnum $type,
        private NoticeContextInterface $context,
    ) {
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getChannel(): NoticeChannelEnum
    {
        return $this->channel;
    }

    public function getType(): NoticeTypeEnum
    {
        return $this->type;
    }

    public function getContext(): NoticeContextInterface
    {
        return $this->context;
    }
}
