<?php

namespace App\Presentation\Web\Response\Model;

readonly class CreatedGroupsInfo
{
    public function __construct(
        public int $created,
    ) {
    }
}
