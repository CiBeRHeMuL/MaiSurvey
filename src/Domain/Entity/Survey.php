<?php

namespace App\Domain\Entity;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table('survey')]
class Survey
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(UuidGenerator::class)]
    #[ORM\Column(type: 'uuid', nullable: false)]
    private Uuid|null $id = null;
    #[ORM\Column(name: 'subject_id', type: 'uuid', nullable: false)]
    private Uuid $subjectId;
    #[ORM\Column(name: 'teacher_id', type: 'uuid', nullable: true)]
    private Uuid|null $teacherId = null;
    #[ORM\Column(name: 'actual_to', type: 'datetime_immutable', nullable: false)]
    private DateTimeImmutable $actualTo;
    #[ORM\Column(name: 'created_at', type: 'datetime_immutable', nullable: false, options: ['default' => 'CURRENT_TIMESTAMP'])]
    private DateTimeImmutable $createdAt;

    #[ORM\ManyToOne(targetEntity: Subject::class)]
    #[ORM\JoinColumn(name: 'subject_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Subject $subject;
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'teacher_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private User|null $teacher = null;
    /** @var Collection<SurveyItem> $items */
    #[ORM\OneToMany(targetEntity: SurveyItem::class, mappedBy: 'survey')]
    private Collection $items;

    public function __construct()
    {
        $this->items = new ArrayCollection();
    }

    public function getId(): Uuid|null
    {
        return $this->id;
    }

    public function setId(Uuid $id): Survey
    {
        $this->id = $id;
        return $this;
    }

    public function getSubjectId(): Uuid
    {
        return $this->subjectId;
    }

    public function setSubjectId(Uuid $subjectId): Survey
    {
        $this->subjectId = $subjectId;
        return $this;
    }

    public function getTeacherId(): Uuid|null
    {
        return $this->teacherId;
    }

    public function setTeacherId(Uuid|null $teacherId): Survey
    {
        $this->teacherId = $teacherId;
        return $this;
    }

    public function getActualTo(): DateTimeImmutable
    {
        return $this->actualTo;
    }

    public function setActualTo(DateTimeImmutable $actualTo): Survey
    {
        $this->actualTo = $actualTo;
        return $this;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): Survey
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getSubject(): Subject
    {
        return $this->subject;
    }

    public function setSubject(Subject $subject): Survey
    {
        $this->subject = $subject;
        return $this;
    }

    public function getTeacher(): User|null
    {
        return $this->teacher;
    }

    public function setTeacher(User|null $teacher): Survey
    {
        $this->teacher = $teacher;
        return $this;
    }

    public function getItems(): Collection
    {
        return $this->items;
    }

    public function setItems(Collection $items): Survey
    {
        $this->items = $items;
        return $this;
    }
}
