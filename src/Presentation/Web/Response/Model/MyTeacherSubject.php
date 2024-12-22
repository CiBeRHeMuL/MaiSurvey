<?php

namespace App\Presentation\Web\Response\Model;

use App\Domain\Entity\MyTeacherSubject as DomainMyTeacherSubject;
use App\Domain\Enum\TeacherSubjectTypeEnum;
use App\Presentation\Web\OpenApi\Attribute as LOA;

readonly class MyTeacherSubject
{
    public function __construct(
        public Subject $subject,
        #[LOA\Enum(TeacherSubjectTypeEnum::class)]
        public string $type,
        public int $students_count,
    ) {
    }

    public static function fromTeacherSubject(DomainMyTeacherSubject $subject): self
    {
        return new self(
            Subject::fromSubject($subject->getTeacherSubject()->getSubject()),
            $subject->getTeacherSubject()->getType()->value,
            $subject->getStudentsCount(),
        );
    }
}
