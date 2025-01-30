<?php

namespace App\Presentation\Web\Response\Model;

use App\Domain\Entity\MySurveyItem;
use App\Domain\Entity\SurveyItem as DomainSurveyItem;
use App\Domain\Enum\SurveyItemTypeEnum;
use App\Domain\Enum\TeacherSubjectTypeEnum;
use App\Presentation\Web\OpenApi\Attribute as LOA;
use App\Presentation\Web\Response\Model\SurveyItemData\Factory\SurveyItemDataFactory;
use App\Presentation\Web\Response\Model\SurveyItemData\ItemDataInterface;

readonly class SurveyItem
{
    /**
     * @param string $id
     * @param string $survey_id
     * @param bool $answer_required
     * @param string $type
     * @param string $text
     * @param int $position
     * @param ItemDataInterface $data
     */
    public function __construct(
        public string $id,
        public string $survey_id,
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

    public static function fromItem(DomainSurveyItem $item): self
    {
        return new self(
            $item->getId()->toRfc4122(),
            $item->getSurveyId()->toRfc4122(),
            $item->isAnswerRequired(),
            $item->getType()->value,
            $item->getText(),
            $item->getPosition(),
            SurveyItemDataFactory::fromItemData($item->getData()),
            $item->getSubjectType()?->value,
        );
    }

    public static function fromMyItem(MySurveyItem $item): self
    {
        return self::fromItem($item->getSurveyItem());
    }
}
