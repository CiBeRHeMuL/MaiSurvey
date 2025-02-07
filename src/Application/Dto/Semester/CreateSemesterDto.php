<?php

namespace App\Application\Dto\Semester;

use Symfony\Component\Validator\Constraints as Assert;

readonly class CreateSemesterDto
{
    public function __construct(
        #[Assert\GreaterThanOrEqual(2000)]
        #[Assert\LessThanOrEqual(2100)]
        #[Assert\Type('integer')]
        public int $year,
        #[Assert\Type('boolean')]
        public bool $spring,
    ) {
    }
}
