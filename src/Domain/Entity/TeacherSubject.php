<?php

namespace App\Domain\Entity;

use App\Domain\Enum\TeacherSubjectTypeEnum;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table('teacher_subject')]
#[ORM\UniqueConstraint(columns: ['teacher_id', 'subject_id', 'type'])]
class TeacherSubject
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[ORM\Column(type: 'uuid', nullable: false)]
    private Uuid $id;
    #[ORM\Column(name: 'teacher_id', type: 'uuid', nullable: false)]
    private Uuid $teacherId;
    #[ORM\Column(name: 'subject_id', type: 'uuid', nullable: false)]
    private Uuid $subjectId;
    #[ORM\Column(type: 'string', length: 255, nullable: false, enumType: TeacherSubjectTypeEnum::class)]
    private TeacherSubjectTypeEnum $type;
    #[ORM\Column(name: 'created_at', type: 'datetime_immutable', nullable: false, options: ['default' => 'CURRENT_TIMESTAMP'])]
    private DateTimeImmutable $createdAt;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'teachingSubjects')]
    #[ORM\JoinColumn(name: 'teacher_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private User $teacher;
    #[ORM\ManyToOne(targetEntity: Subject::class, inversedBy: 'teacherSubjects')]
    #[ORM\JoinColumn(name: 'subject_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Subject $subject;
    /** @var Collection<StudentSubject> $students */
    #[ORM\OneToMany(targetEntity: StudentSubject::class, mappedBy: 'teacherSubject')]
    private Collection $students;

    public function __construct()
    {
        $this->students = new ArrayCollection([]);
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function setId(Uuid $id): TeacherSubject
    {
        $this->id = $id;
        return $this;
    }

    public function getTeacherId(): Uuid
    {
        return $this->teacherId;
    }

    public function setTeacherId(Uuid $teacherId): TeacherSubject
    {
        $this->teacherId = $teacherId;
        return $this;
    }

    public function getSubjectId(): Uuid
    {
        return $this->subjectId;
    }

    public function setSubjectId(Uuid $subjectId): TeacherSubject
    {
        $this->subjectId = $subjectId;
        return $this;
    }

    public function getType(): TeacherSubjectTypeEnum
    {
        return $this->type;
    }

    public function setType(TeacherSubjectTypeEnum $type): TeacherSubject
    {
        $this->type = $type;
        return $this;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): TeacherSubject
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getTeacher(): User
    {
        return $this->teacher;
    }

    public function setTeacher(User $teacher): TeacherSubject
    {
        $this->teacher = $teacher;
        return $this;
    }

    public function getSubject(): Subject
    {
        return $this->subject;
    }

    public function setSubject(Subject $subject): TeacherSubject
    {
        $this->subject = $subject;
        return $this;
    }

    public function getStudents(): Collection
    {
        return $this->students;
    }

    public function setStudents(Collection $students): TeacherSubject
    {
        $this->students = $students;
        return $this;
    }
}
