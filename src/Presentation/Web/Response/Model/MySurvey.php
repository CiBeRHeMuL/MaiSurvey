<?php

namespace App\Presentation\Web\Response\Model;

use App\Domain\Entity\MySurvey as DomainMySurvey;
use OpenApi\Attributes as OA;

readonly class MySurvey
{
    /**
     * @param LiteSurvey $survey
     * @param bool $completed
     * @param string|null $completed_at
     * @param array $items
     */
    public function __construct(
        public LiteSurvey $survey,
        public bool $completed,
        #[OA\Property(format: 'date-time')]
        public string|null $completed_at,
        public array $items,
    ) {
    }

    public static function fromMySurvey(DomainMySurvey $survey): self
    {
        return new self(
            LiteSurvey::fromMySurvey($survey),
            $survey->isCompleted(),
            $survey->getCompletedAt()?->format(DATE_RFC3339),
            array_map(
                MySurveyItem::fromItem(...),
                $survey->getMyItems()->toArray(),
            ),
        );
    }
}
