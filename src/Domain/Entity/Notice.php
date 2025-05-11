<?php

namespace App\Domain\Entity;

use App\Domain\Dto\Notice\NoticeContextInterface;
use App\Domain\Enum\NoticeChannelEnum;
use App\Domain\Enum\NoticeStatusEnum;
use App\Domain\Enum\NoticeTypeEnum;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'notice')]
#[ORM\Index(columns: ['user_id', 'status'])]
#[ORM\Index(columns: ['user_id', 'type'])]
#[ORM\Index(columns: ['user_id', 'channel'])]
#[ORM\Index(columns: ['recipient_id'])]
class Notice
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[ORM\Column(type: 'uuid', nullable: false)]
    private Uuid $id;
    #[ORM\Column(type: 'string', length: 255, nullable: false, enumType: NoticeTypeEnum::class)]
    private NoticeTypeEnum $type;
    #[ORM\Column(type: 'string', length: 255, nullable: false, enumType: NoticeChannelEnum::class)]
    private NoticeChannelEnum $channel;
    #[ORM\Column(name: 'user_id', type: 'uuid', nullable: false)]
    private Uuid $userId;
    #[ORM\Column(
        type: 'string',
        length: 255,
        nullable: false,
        enumType: NoticeStatusEnum::class,
        options: ['default' => NoticeStatusEnum::Created->value],
    )]
    private NoticeStatusEnum $status = NoticeStatusEnum::Created;
    #[ORM\Column(type: 'notice_context', nullable: false, options: ['jsonb' => true])]
    private NoticeContextInterface $context;
    #[ORM\Column(name: 'sent_at', type: 'datetime_immutable', nullable: true)]
    private DateTimeImmutable|null $sentAt = null;
    #[ORM\Column(name: 'delivered_at', type: 'datetime_immutable', nullable: true)]
    private DateTimeImmutable|null $deliveredAt = null;
    #[ORM\Column(type: 'text', nullable: false)]
    private string $text;
    #[ORM\Column(name: 'recipient_id', type: 'string', length: 255, nullable: false)]
    private string $recipientId;
    #[ORM\Column(name: 'send_error', type: 'text', nullable: true)]
    private string|null $sendError = null;
    #[ORM\Column(name: 'external_id', type: 'string', length: 255, nullable: true)]
    private string|null $externalId = null;
    #[ORM\Column(name: 'created_at', type: 'datetime_immutable', nullable: false, options: ['default' => 'CURRENT_TIMESTAMP'])]
    private DateTimeImmutable $createdAt;
    #[ORM\Column(name: 'updated_at', type: 'datetime_immutable', nullable: false, options: ['default' => 'CURRENT_TIMESTAMP'])]
    private DateTimeImmutable $updatedAt;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private User $user;

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function setId(Uuid $id): Notice
    {
        $this->id = $id;
        return $this;
    }

    public function getType(): NoticeTypeEnum
    {
        return $this->type;
    }

    public function setType(NoticeTypeEnum $type): Notice
    {
        $this->type = $type;
        return $this;
    }

    public function getChannel(): NoticeChannelEnum
    {
        return $this->channel;
    }

    public function setChannel(NoticeChannelEnum $channel): Notice
    {
        $this->channel = $channel;
        return $this;
    }

    public function getUserId(): Uuid
    {
        return $this->userId;
    }

    public function setUserId(Uuid $userId): Notice
    {
        $this->userId = $userId;
        return $this;
    }

    public function getStatus(): NoticeStatusEnum
    {
        return $this->status;
    }

    public function setStatus(NoticeStatusEnum $status): Notice
    {
        $this->status = $status;
        return $this;
    }

    public function getContext(): NoticeContextInterface
    {
        return $this->context;
    }

    public function setContext(NoticeContextInterface $context): Notice
    {
        $this->context = $context;
        return $this;
    }

    public function getSentAt(): ?DateTimeImmutable
    {
        return $this->sentAt;
    }

    public function setSentAt(?DateTimeImmutable $sentAt): Notice
    {
        $this->sentAt = $sentAt;
        return $this;
    }

    public function getDeliveredAt(): ?DateTimeImmutable
    {
        return $this->deliveredAt;
    }

    public function setDeliveredAt(?DateTimeImmutable $deliveredAt): Notice
    {
        $this->deliveredAt = $deliveredAt;
        return $this;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): Notice
    {
        $this->text = $text;
        return $this;
    }

    public function getRecipientId(): string
    {
        return $this->recipientId;
    }

    public function setRecipientId(string $recipientId): Notice
    {
        $this->recipientId = $recipientId;
        return $this;
    }

    public function getSendError(): ?string
    {
        return $this->sendError;
    }

    public function setSendError(?string $sendError): Notice
    {
        $this->sendError = $sendError;
        return $this;
    }

    public function getExternalId(): ?string
    {
        return $this->externalId;
    }

    public function setExternalId(?string $externalId): Notice
    {
        $this->externalId = $externalId;
        return $this;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): Notice
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTimeImmutable $updatedAt): Notice
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): Notice
    {
        $this->user = $user;
        return $this;
    }

    public function markAsSending(): Notice
    {
        $this->status = NoticeStatusEnum::Sending;
        return $this;
    }

    public function markAsSent(string|null $externalId = null): Notice
    {
        $this->status = NoticeStatusEnum::Sent;
        $this->sentAt = new DateTimeImmutable();
        $this->externalId = $externalId;
        return $this;
    }

    public function markAsDelivered(): Notice
    {
        $this->status = NoticeStatusEnum::Delivered;
        $this->deliveredAt = new DateTimeImmutable();
        return $this;
    }

    public function markAsSendFailed(string $sendError): Notice
    {
        $this->status = NoticeStatusEnum::SendFailed;
        $this->sendError = $sendError;
        return $this;
    }
}
