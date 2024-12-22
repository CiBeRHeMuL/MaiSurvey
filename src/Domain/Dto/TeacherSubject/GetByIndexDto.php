<?php

namespace App\Domain\Dto\TeacherSubject;

use App\Domain\Enum\TeacherSubjectTypeEnum;
use Symfony\Component\Uid\Uuid;

readonly class GetByIndexDto
{
    public function __construct(
        private Uuid $teacherId,
        private Uuid $subjectId,
        private TeacherSubjectTypeEnum $type,
    ) {
    }

    public function getTeacherId(): Uuid
    {
        return $this->teacherId;
    }

    public function getSubjectId(): Uuid
    {
        return $this->subjectId;
    }

    public function getType(): TeacherSubjectTypeEnum
    {
        return $this->type;
    }
}
