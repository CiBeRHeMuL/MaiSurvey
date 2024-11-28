<?php

namespace App\Presentation\Web\Response\Model;

readonly class CreatedUserData
{
    public function __construct(
        public int $created,
    ) {
    }
}
