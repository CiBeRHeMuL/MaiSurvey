<?php

namespace App\Domain\Dto\TeacherSubject;

use App\Domain\Entity\Subject;
use App\Domain\Entity\User;
use App\Domain\Enum\TeacherSubjectTypeEnum;

readonly class CreateTeacherSubjectDto
{
    public function __construct(
        private User $teacher,
        private Subject $subject,
        private TeacherSubjectTypeEnum $type,
    ) {
    }

    public function getTeacher(): User
    {
        return $this->teacher;
    }

    public function getSubject(): Subject
    {
        return $this->subject;
    }

    public function getType(): TeacherSubjectTypeEnum
    {
        return $this->type;
    }
}
