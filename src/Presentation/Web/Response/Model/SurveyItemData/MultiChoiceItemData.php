<?php

namespace App\Presentation\Web\Response\Model\SurveyItemData;

use App\Domain\Enum\SurveyItemTypeEnum;
use App\Presentation\Web\OpenApi\Attribute as LOA;
use App\Presentation\Web\Response\Model\Choice;

readonly class MultiChoiceItemData implements ItemDataInterface
{
    /**
     * @param value-of<SurveyItemTypeEnum> $type
     * @param Choice[] $choices
     */
    public function __construct(
        #[LOA\Enum(SurveyItemTypeEnum::class)]
        public string $type,
        public array $choices,
    ) {
    }

    public function getType(): string
    {
        return $this->type;
    }
}
