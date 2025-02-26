<?php

namespace App\Domain\Entity;

use App\Domain\Dto\SurveyStatItem\StatDataInterface;
use App\Domain\Enum\SurveyItemTypeEnum;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table('survey_stat_item')]
class SurveyStatItem
{
    #[ORM\Id]
    #[ORM\Column(name: 'id', type: 'uuid', nullable: false)]
    private Uuid $id;
    #[ORM\Column(name: 'survey_id', type: 'uuid', nullable: false)]
    private Uuid $surveyId;
    #[ORM\Column(name: 'available_count', type: 'integer', nullable: false)]
    private int $availableCount;
    #[ORM\Column(name: 'completed_count', type: 'integer', nullable: false)]
    private int $completedCount;
    /**
     * @var StatDataInterface[] $stats
     */
    #[ORM\Column(type: 'stat_data[]', nullable: false, options: ['jsonb' => true])]
    private array $stats;
    #[ORM\Column(type: 'string', length: 255, nullable: false, enumType: SurveyItemTypeEnum::class)]
    private SurveyItemTypeEnum $type;
    #[ORM\Column(type: 'integer', nullable: false)]
    private int $position;

    #[ORM\ManyToOne(targetEntity: SurveyStat::class, inversedBy: 'items')]
    #[ORM\JoinColumn(name: 'survey_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private SurveyStat $survey;
    #[ORM\OneToOne(targetEntity: SurveyItem::class)]
    #[ORM\JoinColumn(name: 'id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private SurveyItem $item;

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function setId(Uuid $id): SurveyStatItem
    {
        $this->id = $id;
        return $this;
    }

    public function getSurveyId(): Uuid
    {
        return $this->surveyId;
    }

    public function setSurveyId(Uuid $surveyId): SurveyStatItem
    {
        $this->surveyId = $surveyId;
        return $this;
    }

    public function getAvailableCount(): int
    {
        return $this->availableCount;
    }

    public function setAvailableCount(int $availableCount): SurveyStatItem
    {
        $this->availableCount = $availableCount;
        return $this;
    }

    public function getCompletedCount(): int
    {
        return $this->completedCount;
    }

    public function setCompletedCount(int $completedCount): SurveyStatItem
    {
        $this->completedCount = $completedCount;
        return $this;
    }

    public function getStats(): array
    {
        return $this->stats;
    }

    /**
     * @param StatDataInterface[] $stats
     *
     * @return $this
     */
    public function setStats(array $stats): SurveyStatItem
    {
        $this->stats = $stats;
        return $this;
    }

    public function getType(): SurveyItemTypeEnum
    {
        return $this->type;
    }

    public function setType(SurveyItemTypeEnum $type): SurveyStatItem
    {
        $this->type = $type;
        return $this;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): SurveyStatItem
    {
        $this->position = $position;
        return $this;
    }

    public function getSurvey(): SurveyStat
    {
        return $this->survey;
    }

    public function setSurvey(SurveyStat $survey): SurveyStatItem
    {
        $this->survey = $survey;
        return $this;
    }

    public function getItem(): SurveyItem
    {
        return $this->item;
    }

    public function setItem(SurveyItem $item): SurveyStatItem
    {
        $this->item = $item;
        return $this;
    }
}
