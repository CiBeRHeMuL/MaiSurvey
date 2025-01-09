<?php

namespace App\Presentation\Web\Response\Model\SurveyItemData;

use App\Domain\Enum\SurveyItemTypeEnum;
use App\Presentation\Web\OpenApi\Attribute as LOA;

readonly class RatingItemData implements ItemDataInterface
{
    /**
     * @param value-of<SurveyItemTypeEnum> $type
     * @param int $min
     * @param int $max
     */
    public function __construct(
        #[LOA\Enum(SurveyItemTypeEnum::class)]
        public string $type,
        public int $min,
        public int $max,
    ) {
    }

    public function getType(): string
    {
        return $this->type;
    }
}
