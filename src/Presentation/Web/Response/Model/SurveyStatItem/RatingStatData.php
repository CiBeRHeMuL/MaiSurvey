<?php

namespace App\Presentation\Web\Response\Model\SurveyStatItem;

readonly class RatingStatData implements StatDataInterface
{
    /**
     * @param string $type
     * @param string|null $teacher_id
     * @param string|null $teacher_name
     * @param int $completed_count
     * @param int $available_count
     * @param RatingCount[] $counts
     * @param float $average
     */
    public function __construct(
        public string $type,
        public string|null $teacher_id,
        public string|null $teacher_name,
        public int $completed_count,
        public int $available_count,
        public array $counts,
        public float $average,
    ) {
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getTeacherId(): string|null
    {
        return $this->teacher_id;
    }

    public function getTeacherName(): string|null
    {
        return $this->teacher_name;
    }

    public function getCompletedCount(): int
    {
        return $this->completed_count;
    }

    public function getAvailableCount(): int
    {
        return $this->available_count;
    }

    public function getCounts(): array
    {
        return $this->counts;
    }

    public function getAverage(): float
    {
        return $this->average;
    }
}
