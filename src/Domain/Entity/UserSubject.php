<?php

namespace App\Domain\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table('user_subject')]
class UserSubject
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', nullable: false)]
    private Uuid $userId;
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', nullable: false)]
    private Uuid $subjectId;
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', nullable: false)]
    private Uuid $teacherId;
    #[ORM\Column(name: 'actual_from', type: 'datetime_immutable', nullable: false)]
    private DateTimeImmutable $actualFrom;
    #[ORM\Column(name: 'actual_to', type: 'datetime_immutable', nullable: false)]
    private DateTimeImmutable $actualTo;
    #[ORM\Column(name: 'created_at', type: 'datetime_immutable', nullable: false, options: ['default' => 'CURRENT_TIMESTAMP'])]
    private DateTimeImmutable $createdAt;
    #[ORM\Column(name: 'updated_at', type: 'datetime_immutable', nullable: false, options: ['default' => 'CURRENT_TIMESTAMP'])]
    private DateTimeImmutable $updatedAt;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'studyingSubjects')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private User $user;
    #[ORM\ManyToOne(targetEntity: Subject::class, inversedBy: 'users')]
    #[ORM\JoinColumn(name: 'subject_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Subject $subject;
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'teachingSubjects')]
    #[ORM\JoinColumn(name: 'teacher_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private User $teacher;

    public function getUserId(): Uuid
    {
        return $this->userId;
    }

    public function setUserId(Uuid $userId): UserSubject
    {
        $this->userId = $userId;
        return $this;
    }

    public function getSubjectId(): Uuid
    {
        return $this->subjectId;
    }

    public function setSubjectId(Uuid $subjectId): UserSubject
    {
        $this->subjectId = $subjectId;
        return $this;
    }

    public function getTeacherId(): Uuid
    {
        return $this->teacherId;
    }

    public function setTeacherId(Uuid $teacherId): UserSubject
    {
        $this->teacherId = $teacherId;
        return $this;
    }

    public function getActualFrom(): DateTimeImmutable
    {
        return $this->actualFrom;
    }

    public function setActualFrom(DateTimeImmutable $actualFrom): UserSubject
    {
        $this->actualFrom = $actualFrom;
        return $this;
    }

    public function getActualTo(): DateTimeImmutable
    {
        return $this->actualTo;
    }

    public function setActualTo(DateTimeImmutable $actualTo): UserSubject
    {
        $this->actualTo = $actualTo;
        return $this;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): UserSubject
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTimeImmutable $updatedAt): UserSubject
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): UserSubject
    {
        $this->user = $user;
        return $this;
    }

    public function getSubject(): Subject
    {
        return $this->subject;
    }

    public function setSubject(Subject $subject): UserSubject
    {
        $this->subject = $subject;
        return $this;
    }

    public function getTeacher(): User
    {
        return $this->teacher;
    }

    public function setTeacher(User $teacher): UserSubject
    {
        $this->teacher = $teacher;
        return $this;
    }
}
