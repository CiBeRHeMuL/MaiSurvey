<?php

namespace App\Presentation\Web\Response\Model;

use App\Domain\Entity\SurveyStat;

readonly class LiteSurveyStat
{
    public function __construct(
        public int $available_count,
        public int $completed_count,
        public float $rating_average,
    ) {
    }

    public static function fromStat(SurveyStat $stat): self
    {
        return new self(
            $stat->getAvailableCount(),
            $stat->getCompletedCount(),
            round($stat->getRatingAvg(), 2),
        );
    }
}
