<?php

namespace App\Domain\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table('survey_stat')]
class SurveyStat
{
    #[ORM\Id]
    #[ORM\Column(name: 'id', type: 'uuid', nullable: false)]
    private Uuid $id;
    #[ORM\Column(name: 'available_count', type: 'integer', nullable: false)]
    private int $availableCount;
    #[ORM\Column(name: 'completed_count', type: 'integer', nullable: false)]
    private int $completedCount;

    #[ORM\OneToOne(targetEntity: Survey::class)]
    #[ORM\JoinColumn(name: 'id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Survey $survey;
    /** @var Collection<int, SurveyStatItem> $items */
    #[ORM\OneToMany(targetEntity: SurveyStatItem::class, mappedBy: 'survey')]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private Collection $items;

    public function __construct()
    {
        $this->items = new ArrayCollection([]);
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function setId(Uuid $id): SurveyStat
    {
        $this->id = $id;
        return $this;
    }

    public function getAvailableCount(): int
    {
        return $this->availableCount;
    }

    public function setAvailableCount(int $availableCount): SurveyStat
    {
        $this->availableCount = $availableCount;
        return $this;
    }

    public function getCompletedCount(): int
    {
        return $this->completedCount;
    }

    public function setCompletedCount(int $completedCount): SurveyStat
    {
        $this->completedCount = $completedCount;
        return $this;
    }

    public function getSurvey(): Survey
    {
        return $this->survey;
    }

    public function setSurvey(Survey $survey): SurveyStat
    {
        $this->survey = $survey;
        return $this;
    }

    /**
     * @return Collection<int, SurveyStatItem>
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function setItems(Collection $items): SurveyStat
    {
        $this->items = $items;
        return $this;
    }

    public function addItem(SurveyStatItem $item): SurveyStat
    {
        $this->items->add($item);
        return $this;
    }
}
