<?php

namespace App\Presentation\Web\Response\Model;

use App\Domain\Entity\UserSubject as DomainUserSubject;
use OpenApi\Attributes as OA;

readonly class MyUserSubject
{
    public function __construct(
        public Subject $subject,
        public LiteUser $teacher,
        #[OA\Property(format: 'date-time')]
        public string $actual_from,
        #[OA\Property(format: 'date-time')]
        public string $actual_to,
    ) {
    }

    public static function fromUserSubject(DomainUserSubject $subject): self
    {
        return new self(
            Subject::fromSubject($subject->getSubject()),
            LiteUser::fromUser($subject->getTeacher()),
            $subject->getActualFrom()->format(DATE_RFC3339),
            $subject->getActualTo()->format(DATE_RFC3339),
        );
    }
}
