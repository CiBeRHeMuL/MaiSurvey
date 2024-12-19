<?php

namespace App\Presentation\Web\Response\Model;

use App\Domain\Entity\StudentSubject as DomainStudentSubject;
use OpenApi\Attributes as OA;

readonly class StudentSubject
{
    public function __construct(
        public Subject $subject,
        public LiteUser $teacher,
        public LiteUser $user,
        #[OA\Property(format: 'date-time')]
        public string $actual_from,
        #[OA\Property(format: 'date-time')]
        public string $actual_to,
    ) {
    }

    public static function fromStudentSubject(DomainStudentSubject $subject): self
    {
        return new self(
            Subject::fromSubject($subject->getSubject()),
            LiteUser::fromUser($subject->getTeacher()),
            LiteUser::fromUser($subject->getUser()),
            $subject->getActualFrom()->format(DATE_RFC3339),
            $subject->getActualTo()->format(DATE_RFC3339),
        );
    }
}
