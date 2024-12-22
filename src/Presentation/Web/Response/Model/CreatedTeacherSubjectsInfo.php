<?php

namespace App\Presentation\Web\Response\Model;

readonly class CreatedTeacherSubjectsInfo
{
    public function __construct(
        public int $created,
    ) {
    }
}
