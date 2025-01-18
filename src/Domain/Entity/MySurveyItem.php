<?php

namespace App\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\MappedSuperclass]
#[ORM\Table('my_survey_item')]
#[ORM\ChangeTrackingPolicy('DEFERRED_EXPLICIT')]
readonly class MySurveyItem
{
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(type: 'uuid', nullable: false)]
        private Uuid $id,
        #[ORM\Column(name: 'survey_id', type: 'uuid', nullable: false)]
        private Uuid $surveyId,
        #[ORM\Column(name: 'user_id', type: 'uuid', nullable: false)]
        private Uuid $userId,
        #[ORM\Column(name: 'teacher_subject_id', type: 'uuid', nullable: false)]
        private Uuid $teacherSubjectId,
        #[ORM\Column(type: 'integer', nullable: false)]
        private int $position,
        #[ORM\ManyToOne(targetEntity: SurveyItem::class)]
        #[ORM\JoinColumn(name: 'id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
        private SurveyItem $surveyItem,
        #[ORM\ManyToOne(targetEntity: MySurvey::class)]
        #[ORM\JoinColumn(name: 'survey_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
        #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'user_id', nullable: false, onDelete: 'CASCADE')]
        private MySurvey $mySurvey,
        #[ORM\ManyToOne(targetEntity: TeacherSubject::class)]
        #[ORM\JoinColumn(name: 'teacher_subject_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
        private TeacherSubject $teacherSubject,
        #[ORM\ManyToOne(targetEntity: User::class)]
        #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
        private User $user,
    ) {
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getSurveyId(): Uuid
    {
        return $this->surveyId;
    }

    public function getUserId(): Uuid
    {
        return $this->userId;
    }

    public function getTeacherSubjectId(): Uuid
    {
        return $this->teacherSubjectId;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function getSurveyItem(): SurveyItem
    {
        return $this->surveyItem;
    }

    public function getMySurvey(): MySurvey
    {
        return $this->mySurvey;
    }

    public function getTeacherSubject(): TeacherSubject
    {
        return $this->teacherSubject;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
