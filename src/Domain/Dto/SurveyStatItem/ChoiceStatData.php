<?php

namespace App\Domain\Dto\SurveyStatItem;

use AndrewGos\ClassBuilder\Attribute as MA;
use AndrewGos\ClassBuilder\Checker\FieldIsChecker;
use App\Domain\Enum\SurveyItemTypeEnum;
use Symfony\Component\Uid\Uuid;

#[MA\BuildIf(new FieldIsChecker('type', SurveyItemTypeEnum::Choice->value))]
readonly class ChoiceStatData implements StatDataInterface
{
    /**
     * @param SurveyItemTypeEnum $type
     * @param Uuid|null $teacher_id
     * @param string|null $teacher_name
     * @param int $completed_count
     * @param int $available_count
     * @param ChoiceCount[] $counts
     */
    public function __construct(
        public SurveyItemTypeEnum $type,
        public Uuid|null $teacher_id,
        public string|null $teacher_name,
        public int $completed_count,
        public int $available_count,
        #[MA\ArrayType(ChoiceCount::class)]
        public array $counts,
    ) {
    }

    public function getType(): SurveyItemTypeEnum
    {
        return $this->type;
    }

    public function getTeacherId(): Uuid|null
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
}
