<?php

namespace App\Presentation\Web\Response\Model\Common;

readonly class Error
{
    public function __construct(
        public string $slug,
        public string $message,
    ) {
    }
}
