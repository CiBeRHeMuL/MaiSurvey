<?php

namespace App\Domain\Dto\Survey\Complete;

use App\Domain\Dto\SurveyItemAnswer\AnswerDataInterface;
use Symfony\Component\Uid\Uuid;

readonly class CompleteSurveyItemDto
{
    /**
     * @param Uuid $id
     * @param AnswerDataInterface|null $data
     */
    public function __construct(
        private Uuid $id,
        private AnswerDataInterface|null $data,
    ) {
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getData(): AnswerDataInterface|null
    {
        return $this->data;
    }
}
