<?php

namespace App\Domain\Dto\Survey;

use Symfony\Component\Uid\Uuid;

readonly class GetMySurveyByIdDto
{
    public function __construct(
        private Uuid $id,
        private bool|null $completed = null,
    ) {
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getCompleted(): bool|null
    {
        return $this->completed;
    }
}
