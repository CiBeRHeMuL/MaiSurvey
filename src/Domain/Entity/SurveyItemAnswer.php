<?php

namespace App\Domain\Entity;

use App\Domain\Dto\SurveyItemAnswer\AnswerDataInterface;
use App\Domain\Enum\SurveyItemTypeEnum;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table('survey_item_answer')]
#[ORM\Index(columns: ['answer'])]
#[ORM\Index(columns: ['type'])]
class SurveyItemAnswer
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(UuidGenerator::class)]
    #[ORM\Column(type: 'uuid', nullable: false)]
    private Uuid $id;
    #[ORM\Column(name: 'survey_item_id', type: 'uuid', nullable: false)]
    private Uuid $surveyItemId;
    #[ORM\Column(type: 'answer_data', nullable: false, options: ['jsonb' => true])]
    private AnswerDataInterface $answer;
    #[ORM\Column(name: 'created_at', type: 'datetime_immutable', nullable: false, options: ['default' => 'CURRENT_TIMESTAMP'])]
    private DateTimeImmutable $createdAt;
    #[ORM\Column(name: 'teacher_subject_id', type: 'uuid', nullable: true)]
    private Uuid|null $teacherSubjectId = null;
    #[ORM\Column(type: 'string', length: 255, nullable: false, enumType: SurveyItemTypeEnum::class)]
    private SurveyItemTypeEnum $type;

    #[ORM\ManyToOne(targetEntity: SurveyItem::class)]
    #[ORM\JoinColumn(name: 'survey_item_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private SurveyItem $item;
    #[ORM\ManyToOne(targetEntity: TeacherSubject::class)]
    #[ORM\JoinColumn(name: 'teacher_subject_id', referencedColumnName: 'id', nullable: true, onDelete: 'CASCADE')]
    private TeacherSubject|null $teacherSubject = null;

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function setId(Uuid $id): SurveyItemAnswer
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

    public function getTeacherSubjectId(): Uuid|null
    {
        return $this->teacherSubjectId;
    }

    public function setTeacherSubjectId(Uuid|null $teacherSubjectId): SurveyItemAnswer
    {
        $this->teacherSubjectId = $teacherSubjectId;
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

    public function getType(): SurveyItemTypeEnum
    {
        return $this->type;
    }

    public function setType(SurveyItemTypeEnum $type): SurveyItemAnswer
    {
        $this->type = $type;
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

    public function getTeacherSubject(): TeacherSubject|null
    {
        return $this->teacherSubject;
    }

    public function setTeacherSubject(TeacherSubject|null $teacherSubject): SurveyItemAnswer
    {
        $this->teacherSubject = $teacherSubject;
        return $this;
    }
}
