<?php

namespace App\Domain\Dto\Notice;

use AndrewGos\ClassBuilder\Attribute\BuildIf;
use AndrewGos\ClassBuilder\Checker\FieldIsChecker;
use App\Domain\Enum\NoticeTypeEnum;
use Symfony\Component\Uid\Uuid;

#[BuildIf(new FieldIsChecker('type', NoticeTypeEnum::NewSurvey->value))]
readonly class NewSurveyNoticeContext implements NoticeContextInterface
{
    public NoticeTypeEnum $type;

    public function __construct(
        public Uuid $surveyId,
    ) {
        $this->type = NoticeTypeEnum::NewSurvey;
    }

    public function getType(): NoticeTypeEnum
    {
        return $this->type;
    }

    public function getSurveyId(): Uuid
    {
        return $this->surveyId;
    }
}
