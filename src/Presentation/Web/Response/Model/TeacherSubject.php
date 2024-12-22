<?php

namespace App\Presentation\Web\Response\Model;

use App\Domain\Entity\TeacherSubject as DomainTeacherSubject;
use App\Domain\Enum\TeacherSubjectTypeEnum;
use App\Presentation\Web\OpenApi\Attribute as LOA;

readonly class TeacherSubject
{
    public function __construct(
        public Subject $subject,
        public LiteUser $teacher,
        #[LOA\Enum(TeacherSubjectTypeEnum::class)]
        public string $type,
    ) {
    }

    public static function fromTeacherSubject(DomainTeacherSubject $subject): self
    {
        return new self(
            Subject::fromSubject($subject->getSubject()),
            LiteUser::fromUser($subject->getTeacher()),
            $subject->getType()->value,
        );
    }
}
