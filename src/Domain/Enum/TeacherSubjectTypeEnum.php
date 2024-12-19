<?php

namespace App\Domain\Enum;

enum TeacherSubjectTypeEnum: string
{
    case Lecture = 'lecture';
    case PracticalLesson = 'practical_lesson';
    case LaboratoryLesson = 'laboratory_lesson';
    case CoursePaper = 'course_paper';
    
}
