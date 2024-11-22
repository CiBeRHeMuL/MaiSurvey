<?php

namespace App\Domain\Enum;

enum PermissionEnum: string
{
    // Survey
    case SurveyView = 'ROLE_survey.view';
    case SurveyComplete = 'ROLE_survey.complete';
    case SurveyCreate = 'ROLE_survey.create';
    case SurveyUpdate = 'ROLE_survey.update';
    case SurveyDelete = 'ROLE_survey.delete';
    case SurveyViewResult = 'ROLE_survey.view_result';
    case SurveyViewAll = 'ROLE_survey.view_all';
}
