<?php

namespace App\Domain\Dto\Survey;

use App\Domain\Dto\SurveyItem\ItemDataInterface;
use App\Domain\Enum\SurveyItemTypeEnum;
use App\Domain\Enum\TeacherSubjectTypeEnum;

readonly class CreateItemDto
{
    public function __construct(
        private bool $answerRequired,
        private SurveyItemTypeEnum $type,
        private string $text,
        private int $position,
        private ItemDataInterface $data,
        private TeacherSubjectTypeEnum $subjectType,
    ) {
    }

    public function isAnswerRequired(): bool
    {
        return $this->answerRequired;
    }

    public function getType(): SurveyItemTypeEnum
    {
        return $this->type;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function getData(): ItemDataInterface
    {
        return $this->data;
    }

    public function getSubjectType(): TeacherSubjectTypeEnum
    {
        return $this->subjectType;
    }
}
