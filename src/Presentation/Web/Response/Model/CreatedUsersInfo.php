<?php

namespace App\Presentation\Web\Response\Model;

readonly class CreatedUsersInfo
{
    public function __construct(
        public int $created,
    ) {
    }
}
