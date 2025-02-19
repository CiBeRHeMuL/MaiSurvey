<?php

namespace App\Domain\Dto\SurveyTemplate;

readonly class CreateSurveyTemplateDto
{
    /**
     * @param string $title
     * @param string $name
     * @param CreateTemplateItemDto[] $items
     */
    public function __construct(
        private string $title,
        private string $name,
        private array $items,
    ) {
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getItems(): array
    {
        return $this->items;
    }
}
