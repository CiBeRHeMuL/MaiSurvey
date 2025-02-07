<?php

namespace App\Domain\Dto\StudentSubject;

use App\Domain\Dto\Semester\GetSemesterByIndexDto;
use App\Domain\Dto\TeacherSubject\GetTSByIndexRawDto;
use App\Domain\ValueObject\Email;

readonly class GetSSByIndexRawDto
{
    public function __construct(
        private Email $studentEmail,
        private GetTSByIndexRawDto $teacherSubjectDto,
        private GetSemesterByIndexDto $semesterDto,
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

    public function getSemesterDto(): GetSemesterByIndexDto
    {
        return $this->semesterDto;
    }
}
