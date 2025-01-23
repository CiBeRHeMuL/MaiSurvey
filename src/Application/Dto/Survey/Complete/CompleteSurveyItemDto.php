<?php

namespace App\Application\Dto\Survey\Complete;

use Symfony\Component\Validator\Constraints as Assert;

readonly class CompleteSurveyItemDto
{
    /**
     * @param string $id
     * @param AnswerDataDtoInterface|null $data
     */
    public function __construct(
        #[Assert\Uuid(message: 'Значение должно быть uuid')]
        #[Assert\NotBlank(message: 'Значение не должно быть пустым')]
        public string $id,
        public AnswerDataDtoInterface|null $data,
    ) {
    }
}
