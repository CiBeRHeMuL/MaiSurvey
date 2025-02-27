<?php

namespace App\Presentation\Web\Response\Model;

use App\Domain\Entity\SurveyStat as DomainSurveyStat;

readonly class SurveyStat
{
    /**
     * @param string $id
     * @param int $available_count
     * @param int $completed_count
     * @param SurveyStatItem[] $items
     */
    public function __construct(
        public string $id,
        public int $available_count,
        public int $completed_count,
        public array $items,
    ) {
    }

    public static function fromStat(DomainSurveyStat $stat): self
    {
        return new self(
            $stat->getId()->toRfc4122(),
            $stat->getAvailableCount(),
            $stat->getCompletedCount(),
            array_map(
                SurveyStatItem::fromStatItem(...),
                $stat->getItems()->toArray(),
            )
        );
    }
}
