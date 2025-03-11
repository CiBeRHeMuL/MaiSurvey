<?php

namespace App\Presentation\Web\Response\Model;

use App\Domain\Entity\MySurvey as DomainMySurvey;
use App\Domain\Entity\Survey as DomainSurvey;
use App\Domain\Enum\SurveyStatusEnum;

readonly class LiteFullSurvey
{
    /**
     * @param string $id
     * @param string $title
     * @param Subject $subject
     * @param value-of<SurveyStatusEnum> $status
     * @param string|null $actual_to
     * @param string $created_at
     * @param string $update_at
     */
    public function __construct(
        public string $id,
        public string $title,
        public Subject $subject,
        public string $status,
        public string|null $actual_to,
        public string $created_at,
        public string $update_at,
    ) {
    }

    public static function fromSurvey(DomainSurvey $survey): self
    {
        return new self(
            $survey->getId()->toRfc4122(),
            $survey->getTitle(),
            Subject::fromSubject($survey->getSubject()),
            $survey->getStatus()->value,
            $survey->getActualTo()?->format(DATE_RFC3339),
            $survey->getCreatedAt()->format(DATE_RFC3339),
            $survey->getUpdatedAt()->format(DATE_RFC3339),
        );
    }
}
