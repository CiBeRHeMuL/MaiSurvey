<?php

namespace App\Presentation\Web\Response\Model;

use App\Domain\Entity\SurveyStatItem as DomainSurveyStatItem;
use App\Domain\Enum\SurveyItemTypeEnum;
use App\Presentation\Web\Response\Model\SurveyStatItem\Factory\SurveyStatDataFactory;
use App\Presentation\Web\Response\Model\SurveyStatItem\StatDataInterface;

readonly class SurveyStatItem
{
    /**
     * @param string $id
     * @param string $text
     * @param int $available_count
     * @param int $completed_count
     * @param value-of<SurveyItemTypeEnum> $type
     * @param StatDataInterface[] $stats
     */
    public function __construct(
        public string $id,
        public string $text,
        public int $available_count,
        public int $completed_count,
        public string $type,
        public array $stats,
    ) {
    }

    public static function fromStatItem(DomainSurveyStatItem $item): self
    {
        return new self(
            $item->getId()->toRfc4122(),
            $item->getItem()->getText(),
            $item->getAvailableCount(),
            $item->getCompletedCount(),
            $item->getType()->value,
            array_map(
                SurveyStatDataFactory::fromItemData(...),
                $item->getStats(),
                array_fill(0, count($item->getStats()), $item),
            ),
        );
    }
}
