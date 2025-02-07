<?php

namespace App\Presentation\Web\Response\Model;

readonly class CreatedSemestersInfo
{
    public function __construct(
        public int $created,
    ) {
    }
}
