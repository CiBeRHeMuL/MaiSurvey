<?php

namespace App\Domain\Dto\Me;

use App\Domain\Enum\NoticeChannelEnum;
use App\Domain\Enum\NoticeTypeEnum;

readonly class UpdateMeDto
{
    /**
     * @param string $firstName
     * @param string $lastName
     * @param string|null $patronymic
     * @param bool $noticesEnabled
     * @param NoticeTypeEnum[] $noticeTypes
     * @param NoticeChannelEnum[] $noticeChannels
     */
    public function __construct(
        private string $firstName,
        private string $lastName,
        private string|null $patronymic,
        private bool $noticesEnabled,
        private array $noticeTypes,
        private array $noticeChannels,
    ) {
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getPatronymic(): ?string
    {
        return $this->patronymic;
    }

    public function isNoticesEnabled(): bool
    {
        return $this->noticesEnabled;
    }

    public function getNoticeTypes(): array
    {
        return $this->noticeTypes;
    }

    public function getNoticeChannels(): array
    {
        return $this->noticeChannels;
    }
}
