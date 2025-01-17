<?php

namespace App\Domain\Dto\StudentSubject;

use App\Domain\Dto\TeacherSubject\GetTSByIndexRawDto;
use App\Domain\ValueObject\Email;
use DateTimeImmutable;

readonly class GetSSByIntersectionRawDto
{
    public function __construct(
        private Email $studentEmail,
        private GetTSByIndexRawDto $teacherSubjectDto,
        private DateTimeImmutable $actualFrom,
        private DateTimeImmutable $actualTo,
    ) {
    }

    public function getStudentEmail(): Email
    {
        return $this->studentEmail;
    }

    public function getTeacherSubjectDto(): GetTSByIndexRawDto
    {
        return $this->teacherSubjectDto;
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
