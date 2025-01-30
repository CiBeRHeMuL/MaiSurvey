<?php

namespace App\Presentation\Web\Response\Model;

use App\Domain\Entity\MySurveyItem as DomainMySurveyItem;

readonly class MySurveyItem
{
    public function __construct(
        public SurveyItem $item,
        public TeacherSubject|null $teacher_subject,
    ) {
    }

    public static function fromItem(DomainMySurveyItem $item): self
    {
        return new self(
            SurveyItem::fromMyItem($item),
            $item->getTeacherSubject() !== null
                ? TeacherSubject::fromTeacherSubject($item->getTeacherSubject())
                : null,
        );
    }
}
