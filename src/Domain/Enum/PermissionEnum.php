<?php

namespace App\Domain\Enum;

enum PermissionEnum: string
{
    // Survey
    case SurveyView = 'survey.view';
    case SurveyViewMy = 'survey.view_my';
    case SurveyComplete = 'survey.complete';
    case SurveyCreate = 'survey.create';
    case SurveyUpdate = 'survey.update';
    case SurveyDelete = 'survey.delete';
    case SurveyViewResult = 'survey.view_result';
    case SurveyViewAll = 'survey.view_all';

    // User
    case UserCreate = 'user.create';
    case UserView = 'user.view';
    case UserExport = 'user.export';
    case UserUpdateAll = 'user.update_all';

    // User Data
    case UserDataImport = 'user_data.import';
    case UserDataViewAll = 'user_data.view_all';

    // Group
    case GroupCreate = 'group.create';
    case GroupImport = 'group.import';

    // Subject
    case SubjectCreate = 'subject.create';
    case SubjectImport = 'subject.import';

    // Student Subject
    case StudentSubjectViewAll = 'student_subject.view_all';
    case StudentSubjectViewMy = 'student_subject.view_my';
    case StudentSubjectImport = 'student_subject.import';

    // Role
    case RoleViewAll = 'role.view_all';

    // Teacher Subject
    case TeacherSubjectViewAll = 'teacher_subject.view_all';
    case TeacherSubjectViewMy = 'teacher_subject.view_my';
    case TeacherSubjectImport = 'teacher_subject.import';

    // Semester
    case SemesterCreate = 'semester.create';

    // Survey Template
    case SurveyTemplateView = 'survey_template.view';
    case SurveyTemplateCreate = 'survey_template.create';
    case SurveyTemplateUpdate = 'survey_template.update';
    case SurveyTemplateDelete = 'survey_template.delete';
    case SurveyTemplateViewAll = 'survey_template.view_all';

    // Survey Stat
    case SurveyStatView = 'survey_stat.view';
}
