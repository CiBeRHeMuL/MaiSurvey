<?php

namespace App\Presentation\Web\Response\Model;

readonly class CreatedUserDataInfo
{
    public function __construct(
        public int $created,
    ) {
    }
}
