<?php

namespace App\Domain\Dto\SurveyItemAnswer;

use App\Domain\Entity\SurveyItem;
use App\Domain\Entity\TeacherSubject;

readonly class CreateSurveyItemAnswerDto
{
    public function __construct(
        private SurveyItem $surveyItem,
        private TeacherSubject $teacherSubject,
        private AnswerDataInterface $data,
    ) {
    }

    public function getSurveyItem(): SurveyItem
    {
        return $this->surveyItem;
    }

    public function getTeacherSubject(): TeacherSubject
    {
        return $this->teacherSubject;
    }

    public function getData(): AnswerDataInterface
    {
        return $this->data;
    }
}
