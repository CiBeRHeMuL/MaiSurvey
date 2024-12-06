<?php

namespace App\Presentation\Web\Response\Model\SurveyItemData;

use App\Presentation\Web\Response\Model\Choice;

readonly class ChoiceItemData implements ItemDataInterface
{
    /**
     * @param string $type
     * @param Choice[] $choices
     */
    public function __construct(
        public string $type,
        public array $choices,
    ) {
    }

    public function getType(): string
    {
        return $this->type;
    }
}
