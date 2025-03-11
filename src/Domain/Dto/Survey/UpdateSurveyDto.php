<?php

namespace App\Domain\Dto\Survey;

use App\Domain\Entity\Subject;
use App\Domain\Enum\SurveyStatusEnum;
use DateTimeImmutable;

readonly class UpdateSurveyDto
{
    /**
     * @param string $title
     * @param DateTimeImmutable|null $actualTo
     * @param (CreateItemDto|UpdateItemDto)[] $items
     * @param Subject $subject
     * @param SurveyStatusEnum $status
     */
    public function __construct(
        private string $title,
        private DateTimeImmutable|null $actualTo,
        private array $items,
        private Subject $subject,
        private SurveyStatusEnum $status,
    ) {
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getActualTo(): DateTimeImmutable|null
    {
        return $this->actualTo;
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function getSubject(): Subject
    {
        return $this->subject;
    }

    public function getStatus(): SurveyStatusEnum
    {
        return $this->status;
    }
}
