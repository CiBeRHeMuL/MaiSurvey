<?php

namespace App\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\MappedSuperclass]
#[ORM\Table('my_teacher_subject')]
#[ORM\ChangeTrackingPolicy('DEFERRED_EXPLICIT')]
readonly class MyTeacherSubject
{
    #[ORM\Id]
    #[ORM\Column(name: 'teacher_subject_id', type: 'uuid', nullable: false)]
    private Uuid $teacherSubjectId;
    #[ORM\Column(name: 'students_count', type: 'integer', nullable: false)]
    private int $studentsCount;

    #[ORM\OneToOne(targetEntity: TeacherSubject::class)]
    #[ORM\JoinColumn(name: 'teacher_subject_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private TeacherSubject $teacherSubject;

    public function getTeacherSubjectId(): Uuid
    {
        return $this->teacherSubjectId;
    }

    public function getStudentsCount(): int
    {
        return $this->studentsCount;
    }

    public function getTeacherSubject(): TeacherSubject
    {
        return $this->teacherSubject;
    }
}
