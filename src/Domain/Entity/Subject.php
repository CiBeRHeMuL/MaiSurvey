<?php

namespace App\Domain\Entity;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table('subject')]
#[ORM\UniqueConstraint(columns: ['name', 'semester_id'])]
class Subject
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(UuidGenerator::class)]
    #[ORM\Column(type: 'uuid', nullable: false)]
    private Uuid $id;
    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    private string $name;
    #[ORM\Column(name: 'semester_id', type: 'uuid', nullable: false)]
    private Uuid $semesterId;
    #[ORM\Column(name: 'created_at', type: 'datetime_immutable', nullable: false, options: ['default' => 'CURRENT_TIMESTAMP'])]
    private DateTimeImmutable $createdAt;
    #[ORM\Column(name: 'updated_at', type: 'datetime_immutable', nullable: false, options: ['default' => 'CURRENT_TIMESTAMP'])]
    private DateTimeImmutable $updatedAt;

    /** @var Collection<TeacherSubject> $teacherSubjects */
    #[ORM\OneToMany(targetEntity: TeacherSubject::class, mappedBy: 'subject')]
    private Collection $teacherSubjects;
    #[ORM\ManyToOne(targetEntity: Semester::class)]
    #[ORM\JoinColumn(name: 'semester_id', referencedColumnName: 'id', nullable: false)]
    private Semester $semester;

    public function __construct()
    {
        $this->teacherSubjects = new ArrayCollection();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function setId(Uuid $id): Subject
    {
        $this->id = $id;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Subject
    {
        $this->name = $name;
        return $this;
    }

    public function getSemesterId(): Uuid
    {
        return $this->semesterId;
    }

    public function setSemesterId(Uuid $semesterId): Subject
    {
        $this->semesterId = $semesterId;
        return $this;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): Subject
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTimeImmutable $updatedAt): Subject
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getTeacherSubjects(): Collection
    {
        return $this->teacherSubjects;
    }

    public function setTeacherSubjects(Collection $teacherSubjects): Subject
    {
        $this->teacherSubjects = $teacherSubjects;
        return $this;
    }

    public function getSemester(): Semester
    {
        return $this->semester;
    }

    public function setSemester(Semester $semester): Subject
    {
        $this->semester = $semester;
        return $this;
    }
}
