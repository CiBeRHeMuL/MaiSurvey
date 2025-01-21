<?php

namespace App\Domain\Dto\SurveyItem;

use AndrewGos\ClassBuilder\Attribute as MA;
use AndrewGos\ClassBuilder\Checker\FieldIsChecker;
use App\Domain\Enum\SurveyItemTypeEnum;

#[MA\BuildIf(new FieldIsChecker('type', SurveyItemTypeEnum::Comment->value))]
readonly class CommentItemData implements ItemDataInterface
{
    /**
     * @param SurveyItemTypeEnum $type
     * @param string|null $placeholder
     * @param int $max_length
     */
    public function __construct(
        public SurveyItemTypeEnum $type,
        public string|null $placeholder,
        public int $max_length,
    ) {
    }

    public function getType(): SurveyItemTypeEnum
    {
        return $this->type;
    }

    public function getPlaceholder(): string|null
    {
        return $this->placeholder;
    }

    public function getMaxLength(): int
    {
        return $this->max_length;
    }
}
