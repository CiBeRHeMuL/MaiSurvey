<?php

namespace App\Application\Dto\Survey\Create;

use App\Application\OpenApi\Attribute as LOA;
use App\Domain\Enum\SurveyItemTypeEnum;
use Symfony\Component\Validator\Constraints as Assert;

readonly class RatingItemData implements ItemDataInterface
{
    /**
     * @param value-of<SurveyItemTypeEnum> $type
     * @param int $min
     * @param int $max
     */
    public function __construct(
        #[LOA\Enum(SurveyItemTypeEnum::class)]
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        public string $type,
        #[Assert\Type('integer', message: 'Значение должно быть целым числом')]
        public int $min,
        #[Assert\Type('integer', message: 'Значение должно быть целым числом')]
        public int $max,
    ) {
    }

    public function getType(): string
    {
        return $this->type;
    }
}
