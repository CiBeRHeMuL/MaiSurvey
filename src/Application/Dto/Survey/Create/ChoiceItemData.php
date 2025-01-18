<?php

namespace App\Application\Dto\Survey\Create;

use App\Application\OpenApi\Attribute as LOA;
use App\Domain\Enum\SurveyItemTypeEnum;
use Symfony\Component\Validator\Constraints as Assert;

readonly class ChoiceItemData implements ItemDataInterface
{
    /**
     * @param value-of<SurveyItemTypeEnum> $type
     * @param Choice[] $choices
     */
    public function __construct(
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[LOA\Enum(SurveyItemTypeEnum::class)]
        public string $type,
        #[Assert\Type('array', message: 'Значение должно быть массивом')]
        public array $choices,
    ) {
    }

    public function getType(): string
    {
        return $this->type;
    }
}
