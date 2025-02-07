<?php

namespace App\Presentation\Web\Response\Model;

use App\Domain\Entity\StudentSubject as DomainStudentSubject;

readonly class StudentSubject
{
    public function __construct(
        public Subject $subject,
        public LiteUser $teacher,
        public LiteUser $user,
        public Semester $semester,
    ) {
    }

    public static function fromStudentSubject(DomainStudentSubject $subject): self
    {
        return new self(
            Subject::fromSubject($subject->getSubject()),
            LiteUser::fromUser($subject->getTeacher()),
            LiteUser::fromUser($subject->getUser()),
            Semester::fromSemester($subject->getSubject()->getSemester()),
        );
    }
}
