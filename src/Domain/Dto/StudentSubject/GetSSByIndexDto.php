<?php

namespace App\Domain\Dto\StudentSubject;

use Symfony\Component\Uid\Uuid;

readonly class GetSSByIndexDto
{
    public function __construct(
        private Uuid $studentId,
        private Uuid $teacherSubjectId,
    ) {
    }

    public function getStudentId(): Uuid
    {
        return $this->studentId;
    }

    public function getTeacherSubjectId(): Uuid
    {
        return $this->teacherSubjectId;
    }
}
