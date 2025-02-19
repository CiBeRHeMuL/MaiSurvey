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
#[ORM\Table('survey_template_item')]
class SurveyTemplateItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(UuidGenerator::class)]
    #[ORM\Column(type: 'uuid', nullable: false)]
    private Uuid $id;
    #[ORM\Column(name: 'survey_template_id', type: 'uuid', nullable: false)]
    private Uuid $surveyTemplateId;
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
    #[ORM\Column(name: 'subject_type', type: 'string', length: 255, nullable: true, enumType: TeacherSubjectTypeEnum::class)]
    private TeacherSubjectTypeEnum|null $subjectType;

    #[ORM\ManyToOne(targetEntity: SurveyTemplate::class, inversedBy: 'items')]
    #[ORM\JoinColumn(name: 'survey_template_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private SurveyTemplate $surveyTemplate;

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function setId(Uuid $id): SurveyTemplateItem
    {
        $this->id = $id;
        return $this;
    }

    public function getSurveyTemplateId(): Uuid
    {
        return $this->surveyTemplateId;
    }

    public function setSurveyTemplateId(Uuid $surveyTemplateId): SurveyTemplateItem
    {
        $this->surveyTemplateId = $surveyTemplateId;
        return $this;
    }

    public function isAnswerRequired(): bool
    {
        return $this->answerRequired;
    }

    public function setAnswerRequired(bool $answerRequired): SurveyTemplateItem
    {
        $this->answerRequired = $answerRequired;
        return $this;
    }

    public function getType(): SurveyItemTypeEnum
    {
        return $this->type;
    }

    public function setType(SurveyItemTypeEnum $type): SurveyTemplateItem
    {
        $this->type = $type;
        return $this;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): SurveyTemplateItem
    {
        $this->text = $text;
        return $this;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): SurveyTemplateItem
    {
        $this->position = $position;
        return $this;
    }

    public function getData(): ItemDataInterface
    {
        return $this->data;
    }

    public function setData(ItemDataInterface $data): SurveyTemplateItem
    {
        $this->data = $data;
        return $this;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): SurveyTemplateItem
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getSubjectType(): TeacherSubjectTypeEnum|null
    {
        return $this->subjectType;
    }

    public function setSubjectType(TeacherSubjectTypeEnum|null $subjectType): SurveyTemplateItem
    {
        $this->subjectType = $subjectType;
        return $this;
    }

    public function getSurveyTemplate(): SurveyTemplate
    {
        return $this->surveyTemplate;
    }

    public function setSurveyTemplate(SurveyTemplate $surveyTemplate): SurveyTemplateItem
    {
        $this->surveyTemplate = $surveyTemplate;
        return $this;
    }
}
