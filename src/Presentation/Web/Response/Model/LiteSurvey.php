<?php

namespace App\Presentation\Web\Response\Model;

use App\Domain\Entity\MySurvey as DomainMySurvey;
use App\Domain\Entity\Survey as DomainSurvey;

readonly class LiteSurvey
{
    public function __construct(
        public string $id,
        public Subject $subject,
        public LiteUser|null $teacher,
    ) {
    }

    public static function fromSurvey(DomainSurvey $survey): self
    {
        return new self(
            $survey->getId()->toRfc4122(),
            Subject::fromSubject($survey->getSubject()),
            $survey->getTeacher() !== null
                ? LiteUser::fromUser($survey->getTeacher())
                : null,
        );
    }

    public static function fromMySurvey(DomainMySurvey $survey): self
    {
        return new self(
            $survey->getId()->toRfc4122(),
            Subject::fromSubject($survey->getSurvey()->getSubject()),
            $survey->getSurvey()->getTeacher() !== null
                ? LiteUser::fromUser($survey->getSurvey()->getTeacher())
                : null,
        );
    }
}
