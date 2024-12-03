<?php

namespace App\Presentation\Web\Response\Model;

readonly class UpdatedUsersInfo
{
    public function __construct(
        public int $updated,
    ) {
    }
}
