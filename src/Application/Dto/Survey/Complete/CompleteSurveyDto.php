<?php

namespace App\Application\Dto\Survey\Complete;

use Symfony\Component\Uid\Uuid;

readonly class CompleteSurveyDto
{
    /**
     * @param Uuid $id
     * @param CompleteSurveyItemDto[] $answers
     */
    public function __construct(
        public Uuid $id,
        public array $answers,
    ) {
    }
}
