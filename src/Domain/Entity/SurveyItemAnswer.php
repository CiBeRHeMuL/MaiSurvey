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
    private Uuid $id;
    #[ORM\Column(name: 'survey_item_id', type: 'uuid', nullable: false)]
    private Uuid $surveyItemId;
    #[ORM\Column(type: 'answer_data', nullable: true, options: ['jsonb' => true])]
    private AnswerDataInterface|null $answer;
    #[ORM\Column(name: 'created_at', type: 'datetime_immutable', nullable: false, options: ['default' => 'CURRENT_TIMESTAMP'])]
    private DateTimeImmutable $createdAt;
    #[ORM\Column(name: 'teacher_subject_id', type: 'uuid', nullable: false)]
    private Uuid $teacherSubjectId;

    #[ORM\ManyToOne(targetEntity: SurveyItem::class)]
    #[ORM\JoinColumn(name: 'survey_item_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private SurveyItem $item;
    #[ORM\ManyToOne(targetEntity: TeacherSubject::class)]
    #[ORM\JoinColumn(name: 'teacher_subject_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private TeacherSubject $teacherSubject;

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

    public function getTeacherSubjectId(): Uuid
    {
        return $this->teacherSubjectId;
    }

    public function setTeacherSubjectId(Uuid $teacherSubjectId): SurveyItemAnswer
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

    public function getItem(): SurveyItem
    {
        return $this->item;
    }

    public function setItem(SurveyItem $item): SurveyItemAnswer
    {
        $this->item = $item;
        return $this;
    }

    public function getTeacherSubject(): TeacherSubject
    {
        return $this->teacherSubject;
    }

    public function setTeacherSubject(TeacherSubject $teacherSubject): SurveyItemAnswer
    {
        $this->teacherSubject = $teacherSubject;
        return $this;
    }
}
