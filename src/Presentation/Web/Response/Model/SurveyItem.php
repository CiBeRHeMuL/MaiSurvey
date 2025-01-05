<?php

namespace App\Presentation\Web\Response\Model;

use App\Domain\Entity\MySurveyItem;
use App\Domain\Entity\SurveyItem as DomainSurveyItem;
use App\Domain\Enum\SurveyItemTypeEnum;
use App\Presentation\Web\OpenApi\Attribute as LOA;
use App\Presentation\Web\Response\Model\SurveyItemData\ChoiceItemData;
use App\Presentation\Web\Response\Model\SurveyItemData\CommentItemData;
use App\Presentation\Web\Response\Model\SurveyItemData\Factory\SurveyItemDataFactory;
use App\Presentation\Web\Response\Model\SurveyItemData\ItemDataInterface;
use App\Presentation\Web\Response\Model\SurveyItemData\MultiChoiceItemData;

readonly class SurveyItem
{
    /**
     * @param string $id
     * @param string $survey_id
     * @param bool $answer_required
     * @param string $type
     * @param string $text
     * @param int $position
     * @param ChoiceItemData|MultiChoiceItemData|CommentItemData $data
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
        );
    }

    public static function fromMyItem(MySurveyItem $item): self
    {
        return self::fromItem($item->getSurveyItem());
    }
}
