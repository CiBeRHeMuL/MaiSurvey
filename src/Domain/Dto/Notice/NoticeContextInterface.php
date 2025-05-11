<?php

namespace App\Domain\Dto\Notice;

use AndrewGos\ClassBuilder\Attribute\AvailableInheritors;
use App\Domain\Enum\NoticeTypeEnum;

#[AvailableInheritors([
    NewSurveyNoticeContext::class,
])]
interface NoticeContextInterface
{
    public function getType(): NoticeTypeEnum;
}
