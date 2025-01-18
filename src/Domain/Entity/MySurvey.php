<?php

namespace App\Domain\Entity;

use DateTimeImmutable;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\MappedSuperclass]
#[ORM\Table('my_survey')]
#[ORM\ChangeTrackingPolicy('DEFERRED_EXPLICIT')]
readonly class MySurvey
{
    /**
     * @param Uuid $id
     * @param Uuid $userId
     * @param bool $completed
     * @param DateTimeImmutable|null $completedAt
     * @param Survey $survey
     * @param User $user
     * @param Collection<int, MySurveyItem> $myItems
     */
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
        #[ORM\OneToMany(targetEntity: MySurveyItem::class, mappedBy: 'mySurvey')]
        #[ORM\InverseJoinColumn(name: 'id', referencedColumnName: 'survey_id')]
        #[ORM\InverseJoinColumn(name: 'user_id', referencedColumnName: 'user_id')]
        #[ORM\OrderBy(['position' => 'ASC'])]
        private Collection $myItems,
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

    /**
     * @return Collection<int, MySurveyItem>
     */
    public function getMyItems(): Collection
    {
        return $this->myItems;
    }
}
