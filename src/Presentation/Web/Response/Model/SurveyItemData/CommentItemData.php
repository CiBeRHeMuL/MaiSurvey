<?php

namespace App\Presentation\Web\Response\Model\SurveyItemData;

use App\Domain\Enum\SurveyItemTypeEnum;
use App\Presentation\Web\OpenApi\Attribute as LOA;

readonly class CommentItemData implements ItemDataInterface
{
    /**
     * @param value-of<SurveyItemTypeEnum> $type
     * @param string|null $placeholder
     * @param int $max_length
     */
    public function __construct(
        #[LOA\Enum(SurveyItemTypeEnum::class)]
        public string $type,
        public string|null $placeholder,
        public int $max_length,
    ) {
    }

    public function getType(): string
    {
        return $this->type;
    }
}
