<?php

namespace App\Domain\Entity;

use App\Domain\Dto\TelegramUser\ChatId;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table('telegram_user')]
class TelegramUser
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(UuidGenerator::class)]
    #[ORM\Column(type: 'uuid', nullable: false)]
    private Uuid $id;
    #[ORM\Column(name: 'user_id', type: 'uuid', unique: true, nullable: false)]
    private Uuid $userId;
    #[ORM\Column(name: 'chat_id', type: 'telegram_chat_id', unique: true, nullable: false)]
    private ChatId $chatId;
    #[ORM\Column(name: 'created_at', type: 'datetime_immutable', nullable: false, options: ['default' => 'CURRENT_TIMESTAMP'])]
    private DateTimeImmutable $createdAt;
    #[ORM\Column(name: 'updated_at', type: 'datetime_immutable', nullable: false, options: ['default' => 'CURRENT_TIMESTAMP'])]
    private DateTimeImmutable $updatedAt;

    #[ORM\OneToOne(targetEntity: User::class, inversedBy: 'telegramUser')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private User $user;

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function setId(Uuid $id): TelegramUser
    {
        $this->id = $id;
        return $this;
    }

    public function getUserId(): Uuid
    {
        return $this->userId;
    }

    public function setUserId(Uuid $userId): TelegramUser
    {
        $this->userId = $userId;
        return $this;
    }

    public function getChatId(): ChatId
    {
        return $this->chatId;
    }

    public function setChatId(ChatId $chatId): TelegramUser
    {
        $this->chatId = $chatId;
        return $this;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): TelegramUser
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTimeImmutable $updatedAt): TelegramUser
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): TelegramUser
    {
        $this->user = $user;
        return $this;
    }
}
