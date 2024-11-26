<?php

namespace App\Presentation\Web\Response\Model\Common;

readonly class Profile
{
    public function __construct(
        public DbInfo $db,
    ) {
    }
}
