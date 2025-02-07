<?php

namespace App\Domain\Dto\StudentSubject;

use App\Domain\Entity\Semester;
use App\Domain\Entity\TeacherSubject;
use App\Domain\Entity\User;

readonly class CreateStudentSubjectDto
{
    public function __construct(
        private User $student,
        private TeacherSubject $teacherSubject,
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

    public function getSemester(): Semester
    {
        return $this->semester;
    }
}
