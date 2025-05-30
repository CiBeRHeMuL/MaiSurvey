<?php

namespace App\Domain\Dto\SurveyItemAnswer;

use App\Domain\Entity\SurveyItem;
use App\Domain\Entity\TeacherSubject;

readonly class CreateSurveyItemAnswerDto
{
    public function __construct(
        private SurveyItem $surveyItem,
        private TeacherSubject|null $teacherSubject,
        private AnswerDataInterface $answer,
    ) {
    }

    public function getSurveyItem(): SurveyItem
    {
        return $this->surveyItem;
    }

    public function getTeacherSubject(): TeacherSubject|null
    {
        return $this->teacherSubject;
    }

    public function getAnswer(): AnswerDataInterface
    {
        return $this->answer;
    }
}
