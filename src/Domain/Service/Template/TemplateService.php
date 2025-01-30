<?php

namespace App\Domain\Service\Template;

use App\Domain\Entity\MySurveyItem;
use App\Domain\Entity\Survey;
use InvalidArgumentException;

class TemplateService
{
    public function getSurveyTValue(string $value, Survey $survey): mixed
    {
        return match ($value) {
            'subject.name' => $survey->getSubject()->getName(),
            default => throw new InvalidArgumentException("Unknown template '$value'"),
        };
    }

    public function getMySurveyItemTValue(string $value, MySurveyItem $surveyItem): mixed
    {
        return match ($value) {
            'teacher.name' => $surveyItem->getTeacherSubject()?->getTeacher()->getData()->getFullName(),
            default => throw new InvalidArgumentException("Unknown template '$value'"),
        };
    }

    public function putTsIntoMySurveyItem(MySurveyItem &$item): void
    {
        $text = $item->getSurveyItem()->getText();
        $text = preg_replace_callback(
            '/\{([\w.]+)}/ui',
            fn(array $val) => $this->getMySurveyItemTValue($val[1], $item),
            $text,
        );
        $item->getSurveyItem()->setText($text);
    }
}
