<?php

namespace App\Domain\Dto\StudentSubject;

use DateTimeImmutable;
use Symfony\Component\Uid\Uuid;

readonly class GetStudentSubjectByIntersectionDto
{
    public function __construct(
        private Uuid $studentId,
        private Uuid $teacherSubjectId,
        private DateTimeImmutable $actualFrom,
        private DateTimeImmutable $actualTo,
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

    public function getActualFrom(): DateTimeImmutable
    {
        return $this->actualFrom;
    }

    public function getActualTo(): DateTimeImmutable
    {
        return $this->actualTo;
    }
}
