<?php

namespace App\Domain\Dto\Survey;

use App\Domain\Entity\Subject;
use App\Domain\Entity\SurveyTemplate;
use App\Domain\Enum\SurveyStatusEnum;
use DateTimeImmutable;

readonly class CreateSurveyFromTemplateDto
{
    public function __construct(
        private Subject $subject,
        private DateTimeImmutable|null $actualTo,
        private SurveyTemplate $template,
        private SurveyStatusEnum $status,
    ) {
    }

    public function getSubject(): Subject
    {
        return $this->subject;
    }

    public function getActualTo(): DateTimeImmutable
    {
        return $this->actualTo;
    }

    public function getTemplate(): SurveyTemplate
    {
        return $this->template;
    }

    public function getStatus(): SurveyStatusEnum
    {
        return $this->status;
    }
}
