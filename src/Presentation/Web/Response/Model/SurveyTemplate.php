<?php

namespace App\Presentation\Web\Response\Model;

use App\Domain\Entity\SurveyTemplate as DomainSurveyTemplate;

readonly class SurveyTemplate
{
    /**
     * @param string $id
     * @param string $name
     * @param string $title
     * @param SurveyTemplateItem[] $items
     */
    public function __construct(
        public string $id,
        public string $name,
        public string $title,
        public array $items,
    ) {
    }

    public static function fromSurveyTemplate(DomainSurveyTemplate $survey): self
    {
        return new self(
            $survey->getId()->toRfc4122(),
            $survey->getName(),
            $survey->getTitle(),
            array_map(
                SurveyTemplateItem::fromItem(...),
                $survey->getItems()->toArray(),
            ),
        );
    }
}
