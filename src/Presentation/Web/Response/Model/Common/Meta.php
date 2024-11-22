<?php

namespace App\Presentation\Web\Response\Model\Common;

readonly class Meta
{
    public function __construct(
        public string $mode,
    ) {
    }
}
