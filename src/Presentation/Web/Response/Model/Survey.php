<?php

namespace App\Presentation\Web\Response\Model;

use App\Domain\Entity\MySurvey as DomainMySurvey;
use App\Domain\Entity\Survey as DomainSurvey;

readonly class Survey
{
    /**
     * @param string $id
     * @param string $title
     * @param Subject $subject
     * @param SurveyItem[] $items
     */
    public function __construct(
        public string $id,
        public string $title,
        public Subject $subject,
        public array $items,
    ) {
    }

    public static function fromSurvey(DomainSurvey $survey): self
    {
        return new self(
            $survey->getId()->toRfc4122(),
            $survey->getTitle(),
            Subject::fromSubject($survey->getSubject()),
            array_map(
                SurveyItem::fromItem(...),
                $survey->getItems()->toArray(),
            ),
        );
    }

    public static function fromMySurvey(DomainMySurvey $survey): self
    {
        return new self(
            $survey->getId()->toRfc4122(),
            $survey->getSurvey()->getTitle(),
            Subject::fromSubject($survey->getSurvey()->getSubject()),
            array_map(
                SurveyItem::fromMyItem(...),
                $survey->getMyItems()->toArray(),
            ),
        );
    }
}
