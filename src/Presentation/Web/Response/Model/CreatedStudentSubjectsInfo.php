<?php

namespace App\Presentation\Web\Response\Model;

readonly class CreatedStudentSubjectsInfo
{
    public function __construct(
        public int $created,
        public int $skipped,
    ) {
    }
}
