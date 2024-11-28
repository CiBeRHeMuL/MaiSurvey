<?php

namespace App\Presentation\Web\Response\Model;

readonly class CreatedSubjectsInfo
{
    public function __construct(
        public int $created,
    ) {
    }
}
