<?php

namespace App\Domain\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\MappedSuperclass]
#[ORM\Table('my_survey')]
#[ORM\ChangeTrackingPolicy('DEFERRED_EXPLICIT')]
readonly class MySurvey
{
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(name: 'id', type: 'uuid', nullable: false)]
        private Uuid $id,
        #[ORM\Id]
        #[ORM\Column(name: 'user_id', type: 'uuid', nullable: false)]
        private Uuid $userId,
        #[ORM\Column(type: 'boolean', nullable: false)]
        private bool $completed,
        #[ORM\Column(name: 'completed_at', type: 'datetime_immutable', nullable: true)]
        private DateTimeImmutable|null $completedAt,
        #[ORM\ManyToOne(targetEntity: Survey::class)]
        #[ORM\JoinColumn(name: 'id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
        private Survey $survey,
        #[ORM\ManyToOne(targetEntity: User::class)]
        #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
        private User $user,
    ) {
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getUserId(): Uuid
    {
        return $this->userId;
    }

    public function isCompleted(): bool
    {
        return $this->completed;
    }

    public function getCompletedAt(): DateTimeImmutable|null
    {
        return $this->completedAt;
    }

    public function getSurvey(): Survey
    {
        return $this->survey;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
