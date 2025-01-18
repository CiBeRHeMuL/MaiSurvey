<?php

namespace App\Application\Dto\Survey\Create;

use App\Application\OpenApi\Attribute as LOA;
use App\Domain\Enum\SurveyItemTypeEnum;
use Symfony\Component\Validator\Constraints as Assert;

readonly class CommentItemData implements ItemDataInterface
{
    /**
     * @param value-of<SurveyItemTypeEnum> $type
     * @param string|null $placeholder
     */
    public function __construct(
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[LOA\Enum(SurveyItemTypeEnum::class)]
        public string $type,
        #[Assert\Type(['string', 'null'], message: 'Значение должно быть строкой')]
        public string|null $placeholder,
    ) {
    }

    public function getType(): string
    {
        return $this->type;
    }
}
