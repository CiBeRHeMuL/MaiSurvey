<?php

namespace App\Presentation\Web\Response\Model;

use App\Domain\Entity\MySurvey as DomainMySurvey;
use OpenApi\Attributes as OA;

readonly class MySurvey
{
    public function __construct(
        public Survey $survey,
        public bool $completed,
        #[OA\Property(format: 'date-time')]
        public string|null $completed_at,
    ) {
    }

    public static function fromMySurvey(DomainMySurvey $survey): self
    {
        return new self(
            Survey::fromMySurvey($survey),
            $survey->isCompleted(),
            $survey->getCompletedAt()?->format(DATE_RFC3339),
        );
    }
}
