<?php

namespace App\Application\Dto\Survey\Create;

use App\Application\OpenApi\Attribute as LOA;
use App\Domain\Enum\SurveyItemTypeEnum;
use Symfony\Component\Validator\Constraints as Assert;

readonly class MultiChoiceItemData implements ItemDataInterface
{
    /**
     * @param value-of<SurveyItemTypeEnum> $type
     * @param Choice[] $choices
     */
    public function __construct(
        #[LOA\Enum(SurveyItemTypeEnum::class)]
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
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
