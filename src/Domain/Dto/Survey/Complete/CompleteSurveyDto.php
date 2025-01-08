<?php

namespace App\Domain\Dto\Survey\Complete;

use Symfony\Component\Uid\Uuid;

readonly class CompleteSurveyDto
{
    /**
     * @param Uuid $id
     * @param CompleteSurveyItemDto[] $answers
     */
    public function __construct(
        private Uuid $id,
        private array $answers,
    ) {
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getAnswers(): array
    {
        return $this->answers;
    }
}
