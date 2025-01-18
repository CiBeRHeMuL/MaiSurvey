<?php

namespace App\Domain\Dto\Survey;

use DateTimeImmutable;
use Symfony\Component\Uid\Uuid;

readonly class CreateSurveyDto
{
    /**
     * @param string $title
     * @param Uuid $subjectId
     * @param DateTimeImmutable $actualTo
     * @param CreateItemDto[] $items
     */
    public function __construct(
        private string $title,
        private Uuid $subjectId,
        private DateTimeImmutable $actualTo,
        private array $items,
    ) {
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getSubjectId(): Uuid
    {
        return $this->subjectId;
    }

    public function getActualTo(): DateTimeImmutable
    {
        return $this->actualTo;
    }

    public function getItems(): array
    {
        return $this->items;
    }
}
