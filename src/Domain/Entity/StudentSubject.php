<?php

namespace App\Domain\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table('student_subject')]
#[ORM\UniqueConstraint(columns: ['user_id', 'teacher_subject_id'])]
class StudentSubject
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[ORM\Column(type: 'uuid', nullable: false)]
    private Uuid $id;
    #[ORM\Column(type: 'uuid', nullable: false)]
    private Uuid $userId;
    #[ORM\Column(name: 'teacher_subject_id', type: 'uuid', nullable: false)]
    private Uuid $teacherSubjectId;
    #[ORM\Column(name: 'created_at', type: 'datetime_immutable', nullable: false, options: ['default' => 'CURRENT_TIMESTAMP'])]
    private DateTimeImmutable $createdAt;
    #[ORM\Column(name: 'updated_at', type: 'datetime_immutable', nullable: false, options: ['default' => 'CURRENT_TIMESTAMP'])]
    private DateTimeImmutable $updatedAt;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'studyingSubjects')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private User $user;
    #[ORM\ManyToOne(targetEntity: TeacherSubject::class, inversedBy: 'students')]
    #[ORM\JoinColumn(name: 'teacher_subject_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private TeacherSubject $teacherSubject;

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function setId(Uuid $id): StudentSubject
    {
        $this->id = $id;
        return $this;
    }

    public function getUserId(): Uuid
    {
        return $this->userId;
    }

    public function setUserId(Uuid $userId): StudentSubject
    {
        $this->userId = $userId;
        return $this;
    }

    public function getTeacherSubjectId(): Uuid
    {
        return $this->teacherSubjectId;
    }

    public function setTeacherSubjectId(Uuid $teacherSubjectId): StudentSubject
    {
        $this->teacherSubjectId = $teacherSubjectId;
        return $this;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): StudentSubject
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTimeImmutable $updatedAt): StudentSubject
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): StudentSubject
    {
        $this->user = $user;
        return $this;
    }

    public function getSubject(): Subject
    {
        return $this->getTeacherSubject()->getSubject();
    }

    public function getTeacher(): User
    {
        return $this->getTeacherSubject()->getTeacher();
    }

    public function getTeacherSubject(): TeacherSubject
    {
        return $this->teacherSubject;
    }

    public function setTeacherSubject(TeacherSubject $teacherSubject): StudentSubject
    {
        $this->teacherSubject = $teacherSubject;
        return $this;
    }
}
