<?php

namespace App\Domain\Enum;

enum TeacherSubjectTypeEnum: string
{
    case Lecture = 'lecture';
    case PracticalLesson = 'practical_lesson';
    case LaboratoryLesson = 'laboratory_lesson';
    case CoursePaper = 'course_paper';

    public function getName(): string
    {
        return match ($this) {
            self::Lecture => 'Лекции',
            self::PracticalLesson => 'Практические занятия',
            self::LaboratoryLesson => 'Лабораторные работы',
            self::CoursePaper => 'Курсовые работы',
        };
    }
}
