<?php

namespace App\Presentation\Web\Response\Model;

use App\Domain\Entity\SurveyTemplateItem as DomainSurveyTemplateItem;
use App\Domain\Enum\SurveyItemTypeEnum;
use App\Domain\Enum\TeacherSubjectTypeEnum;
use App\Presentation\Web\OpenApi\Attribute as LOA;
use App\Presentation\Web\Response\Model\SurveyItemData\Factory\SurveyItemDataFactory;
use App\Presentation\Web\Response\Model\SurveyItemData\ItemDataInterface;

readonly class SurveyTemplateItem
{
    /**
     * @param string $id
     * @param string $survey_template_id
     * @param bool $answer_required
     * @param string $type
     * @param string $text
     * @param int $position
     * @param ItemDataInterface $data
     * @param string|null $subject_type
     */
    public function __construct(
        public string $id,
        public string $survey_template_id,
        public bool $answer_required,
        #[LOA\Enum(SurveyItemTypeEnum::class)]
        public string $type,
        public string $text,
        public int $position,
        public ItemDataInterface $data,
        #[LOA\Enum(TeacherSubjectTypeEnum::class)]
        public string|null $subject_type,
    ) {
    }

    public static function fromItem(DomainSurveyTemplateItem $item): self
    {
        return new self(
            $item->getId()->toRfc4122(),
            $item->getSurveyTemplateId()->toRfc4122(),
            $item->isAnswerRequired(),
            $item->getType()->value,
            $item->getText(),
            $item->getPosition(),
            SurveyItemDataFactory::fromItemData($item->getData()),
            $item->getSubjectType()?->value,
        );
    }
}
