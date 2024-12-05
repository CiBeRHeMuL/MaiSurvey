<?php

namespace App\Domain\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table('completed_survey')]
class CompletedSurvey
{
    #[ORM\Id]
    #[ORM\Column(name: 'user_id', type: 'uuid', nullable: false)]
    private Uuid $userId;
    #[ORM\Id]
    #[ORM\Column(name: 'survey_id', type: 'uuid', nullable: false)]
    private Uuid $surveyId;
    #[ORM\Column(name: 'created_at', type: 'datetime_immutable', nullable: false, options: ['default' => 'CURRENT_TIMESTAMP'])]
    private DateTimeImmutable $createdAt;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private User $user;
    #[ORM\ManyToOne(targetEntity: Survey::class)]
    #[ORM\JoinColumn(name: 'survey_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Survey $survey;

    public function getUserId(): Uuid
    {
        return $this->userId;
    }

    public function setUserId(Uuid $userId): CompletedSurvey
    {
        $this->userId = $userId;
        return $this;
    }

    public function getSurveyId(): Uuid
    {
        return $this->surveyId;
    }

    public function setSurveyId(Uuid $surveyId): CompletedSurvey
    {
        $this->surveyId = $surveyId;
        return $this;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): CompletedSurvey
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): CompletedSurvey
    {
        $this->user = $user;
        return $this;
    }

    public function getSurvey(): Survey
    {
        return $this->survey;
    }

    public function setSurvey(Survey $survey): CompletedSurvey
    {
        $this->survey = $survey;
        return $this;
    }
}
