<?php

namespace App\Domain\Dto\TeacherSubject;

use App\Domain\Enum\TeacherSubjectTypeEnum;
use App\Domain\ValueObject\Email;

readonly class GetTSByIndexRawDto
{
    public function __construct(
        private Email $teacherEmail,
        private string $subjectName,
        private TeacherSubjectTypeEnum $type,
    ) {
    }

    public function getTeacherEmail(): Email
    {
        return $this->teacherEmail;
    }

    public function getSubjectName(): string
    {
        return $this->subjectName;
    }

    public function getType(): TeacherSubjectTypeEnum
    {
        return $this->type;
    }
}
