<?php

namespace App\Domain\Entity;

use App\Domain\Dto\SurveyItem\ItemDataInterface;
use App\Domain\Enum\SurveyItemTypeEnum;
use App\Domain\Enum\TeacherSubjectTypeEnum;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table('survey_item')]
class SurveyItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(UuidGenerator::class)]
    #[ORM\Column(type: 'uuid', nullable: false)]
    private Uuid $id;
    #[ORM\Column(name: 'survey_id', type: 'uuid', nullable: false)]
    private Uuid $surveyId;
    #[ORM\Column(name: 'answer_required', type: 'boolean', nullable: false, options: ['default' => true])]
    private bool $answerRequired;
    #[ORM\Column(type: 'string', length: 255, nullable: false, enumType: SurveyItemTypeEnum::class)]
    private SurveyItemTypeEnum $type;
    #[ORM\Column(type: 'string', nullable: false)]
    private string $text;
    #[ORM\Column(type: 'integer', nullable: false)]
    private int $position;
    #[ORM\Column(type: 'item_data', nullable: false, options: ['jsonb' => true])]
    private ItemDataInterface $data;
    #[ORM\Column(name: 'created_at', type: 'datetime_immutable', nullable: false, options: ['default' => 'CURRENT_TIMESTAMP'])]
    private DateTimeImmutable $createdAt;
    #[ORM\Column(name: 'subject_type', type: 'string', length: 255, nullable: false, enumType: TeacherSubjectTypeEnum::class)]
    private TeacherSubjectTypeEnum $subjectType;

    #[ORM\ManyToOne(targetEntity: Survey::class, inversedBy: 'items')]
    #[ORM\JoinColumn(name: 'survey_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Survey $survey;

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function setId(Uuid $id): SurveyItem
    {
        $this->id = $id;
        return $this;
    }

    public function getSurveyId(): Uuid
    {
        return $this->surveyId;
    }

    public function setSurveyId(Uuid $surveyId): SurveyItem
    {
        $this->surveyId = $surveyId;
        return $this;
    }

    public function isAnswerRequired(): bool
    {
        return $this->answerRequired;
    }

    public function setAnswerRequired(bool $answerRequired): SurveyItem
    {
        $this->answerRequired = $answerRequired;
        return $this;
    }

    public function getType(): SurveyItemTypeEnum
    {
        return $this->type;
    }

    public function setType(SurveyItemTypeEnum $type): SurveyItem
    {
        $this->type = $type;
        return $this;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): SurveyItem
    {
        $this->text = $text;
        return $this;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): SurveyItem
    {
        $this->position = $position;
        return $this;
    }

    public function getData(): ItemDataInterface
    {
        return $this->data;
    }

    public function setData(ItemDataInterface $data): SurveyItem
    {
        $this->data = $data;
        return $this;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): SurveyItem
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getSubjectType(): TeacherSubjectTypeEnum
    {
        return $this->subjectType;
    }

    public function setSubjectType(TeacherSubjectTypeEnum $subjectType): SurveyItem
    {
        $this->subjectType = $subjectType;
        return $this;
    }

    public function getSurvey(): Survey
    {
        return $this->survey;
    }

    public function setSurvey(Survey $survey): SurveyItem
    {
        $this->survey = $survey;
        return $this;
    }
}
