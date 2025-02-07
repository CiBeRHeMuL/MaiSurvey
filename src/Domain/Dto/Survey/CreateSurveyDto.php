<?php

namespace App\Domain\Dto\Survey;

use App\Domain\Entity\Subject;
use DateTimeImmutable;

readonly class CreateSurveyDto
{
    /**
     * @param string $title
     * @param DateTimeImmutable $actualTo
     * @param CreateItemDto[] $items
     * @param Subject $subject
     */
    public function __construct(
        private string $title,
        private DateTimeImmutable $actualTo,
        private array $items,
        private Subject $subject,
    ) {
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getActualTo(): DateTimeImmutable
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
}
