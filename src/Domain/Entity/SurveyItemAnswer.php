<?php

namespace App\Domain\Entity;

use App\Domain\Dto\SurveyItemAnswer\AnswerDataInterface;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table('survey_item_answer')]
class SurveyItemAnswer
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(UuidGenerator::class)]
    #[ORM\Column(type: 'uuid', nullable: false)]
    private Uuid|null $id = null;
    #[ORM\Column(name: 'survey_item_id', type: 'uuid', nullable: false)]
    private Uuid $surveyItemId;
    #[ORM\Column(type: 'answer_data', nullable: false)]
    private AnswerDataInterface $answer;
    #[ORM\Column(name: 'created_at', type: 'datetime_immutable', nullable: false, options: ['default' => 'CURRENT_TIMESTAMP'])]
    private DateTimeImmutable $createdAt;

    #[ORM\ManyToOne(targetEntity: SurveyItem::class)]
    #[ORM\JoinColumn(name: 'survey_item_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private SurveyItem $item;

    public function getId(): Uuid|null
    {
        return $this->id;
    }

    public function setId(Uuid|null $id): SurveyItemAnswer
    {
        $this->id = $id;
        return $this;
    }

    public function getSurveyItemId(): Uuid
    {
        return $this->surveyItemId;
    }

    public function setSurveyItemId(Uuid $surveyItemId): SurveyItemAnswer
    {
        $this->surveyItemId = $surveyItemId;
        return $this;
    }

    public function getAnswer(): AnswerDataInterface
    {
        return $this->answer;
    }

    public function setAnswer(AnswerDataInterface $answer): SurveyItemAnswer
    {
        $this->answer = $answer;
        return $this;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): SurveyItemAnswer
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getItem(): SurveyItem
    {
        return $this->item;
    }

    public function setItem(SurveyItem $item): SurveyItemAnswer
    {
        $this->item = $item;
        return $this;
    }
}
