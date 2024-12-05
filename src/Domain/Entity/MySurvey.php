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
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', nullable: false)]
    private Uuid $id;
    #[ORM\Column(name: 'user_id', type: 'uuid', nullable: false)]
    private Uuid $userId;
    #[ORM\Column(name: 'subject_id', type: 'uuid', nullable: false)]
    private Uuid $subjectId;
    #[ORM\Column(name: 'teacher_id', type: 'uuid', nullable: true)]
    private Uuid|null $teacherId;
    #[ORM\Column(name: 'actual_to', type: 'datetime_immutable', nullable: false)]
    private DateTimeImmutable $actualTo;
    #[ORM\Column(name: 'created_at', type: 'datetime_immutable', nullable: false, options: ['default' => 'CURRENT_TIMESTAMP'])]
    private DateTimeImmutable $createdAt;
    #[ORM\Column(type: 'boolean', nullable: false)]
    private bool $completed;
    #[ORM\Column(name: 'completed_at', type: 'datetime_immutable', nullable: true)]
    private DateTimeImmutable|null $completedAt;

    #[ORM\ManyToOne(targetEntity: Subject::class)]
    #[ORM\JoinColumn(name: 'subject_id', referencedColumnName: 'id', nullable: false)]
    private Subject $subject;
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'teacher_id', referencedColumnName: 'id', nullable: false)]
    private User|null $teacher;
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false)]
    private User $user;
    /** @var Collection<SurveyItem> $items */
    #[ORM\OneToMany(targetEntity: SurveyItem::class, mappedBy: 'survey')]
    private Collection $items;

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getUserId(): Uuid
    {
        return $this->userId;
    }

    public function getSubjectId(): Uuid
    {
        return $this->subjectId;
    }

    public function getTeacherId(): Uuid|null
    {
        return $this->teacherId;
    }

    public function getActualTo(): DateTimeImmutable
    {
        return $this->actualTo;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function isCompleted(): bool
    {
        return $this->completed;
    }

    public function getCompletedAt(): DateTimeImmutable|null
    {
        return $this->completedAt;
    }

    public function getSubject(): Subject
    {
        return $this->subject;
    }

    public function getTeacher(): User|null
    {
        return $this->teacher;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getItems(): Collection
    {
        return $this->items;
    }
}
