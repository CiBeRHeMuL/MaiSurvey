<?php

namespace App\Domain\Dto\SurveyItemAnswer;

use AndrewGos\ClassBuilder\Attribute as MA;
use AndrewGos\ClassBuilder\Checker\FieldIsChecker;
use App\Domain\Dto\SurveyItem\ItemDataInterface;
use App\Domain\Enum\SurveyItemTypeEnum;

#[MA\BuildIf(new FieldIsChecker('type', SurveyItemTypeEnum::Comment->value))]
readonly class CommentAnswerData implements ItemDataInterface
{
    /**
     * @param SurveyItemTypeEnum $type
     * @param string $comment
     */
    public function __construct(
        public SurveyItemTypeEnum $type,
        public string $comment,
    ) {
    }

    public function getType(): SurveyItemTypeEnum
    {
        return $this->type;
    }

    public function getComment(): string
    {
        return $this->comment;
    }
}
