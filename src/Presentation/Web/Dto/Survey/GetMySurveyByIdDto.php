<?php

namespace App\Presentation\Web\Dto\Survey;

use Symfony\Component\Validator\Constraints as Assert;

readonly class GetMySurveyByIdDto
{
    public function __construct(
        /** Завершен ли опрос */
        #[Assert\Type('boolean', message: 'Значение должно быть булевым')]
        public bool|null $completed = null,
    ) {
    }
}
