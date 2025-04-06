<?php

namespace App\Presentation\Web\Response\Model;

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
     * @param string $updated_at
     * @param LiteSurveyStat|null $stat
     */
    public function __construct(
        public string $id,
        public string $title,
        public Subject $subject,
        public string $status,
        public string|null $actual_to,
        public string $created_at,
        public string $updated_at,
        public LiteSurveyStat|null $stat = null,
    ) {
    }

    public static function fromSurvey(DomainSurvey $survey, bool $withStat = true): self
    {
        return new self(
            $survey->getId()->toRfc4122(),
            $survey->getTitle(),
            Subject::fromSubject($survey->getSubject()),
            $survey->getStatus()->value,
            $survey->getActualTo()?->format(DATE_RFC3339),
            $survey->getCreatedAt()->format(DATE_RFC3339),
            $survey->getUpdatedAt()->format(DATE_RFC3339),
            $withStat && $survey->getStat()
                ? LiteSurveyStat::fromStat($survey->getStat())
                : null,
        );
    }
}
