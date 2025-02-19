<?php

namespace App\Presentation\Web\Response\Model;

use App\Domain\Entity\SurveyTemplate;

readonly class LiteSurveyTemplate
{
    public function __construct(
        public string $id,
        public string $name,
        public string $title,
    ) {
    }

    public static function fromSurveyTemplate(SurveyTemplate $template): self
    {
        return new self(
            $template->getId()->toRfc4122(),
            $template->getName(),
            $template->getTitle(),
        );
    }
}
