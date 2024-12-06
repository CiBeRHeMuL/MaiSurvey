<?php

namespace App\Presentation\Web\Response\Model\SurveyItemData;

readonly class CommentItemData implements ItemDataInterface
{
    public function __construct(
        public string $type,
        public string|null $placeholder,
    ) {
    }

    public function getType(): string
    {
        return $this->type;
    }
}
