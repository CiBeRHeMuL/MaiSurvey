<?php

namespace App\Domain\Dto\StudentSubject;

use App\Domain\Entity\TeacherSubject;
use App\Domain\Entity\User;
use DateTimeImmutable;

readonly class CreateStudentSubjectDto
{
    public function __construct(
        private User $student,
        private TeacherSubject $teacherSubject,
        private DateTimeImmutable $actualFrom,
        private DateTimeImmutable $actualTo,
    ) {
    }

    public function getStudent(): User
    {
        return $this->student;
    }

    public function getTeacherSubject(): TeacherSubject
    {
        return $this->teacherSubject;
    }

    public function getActualFrom(): DateTimeImmutable
    {
        return $this->actualFrom;
    }

    public function getActualTo(): DateTimeImmutable
    {
        return $this->actualTo;
    }
}
