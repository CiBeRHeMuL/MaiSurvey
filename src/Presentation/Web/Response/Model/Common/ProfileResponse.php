<?php

namespace App\Presentation\Web\Response\Model\Common;

readonly class ProfileResponse
{
    public function __construct(
        public Profile $profile,
        public Meta $meta = new Meta('dev'),
    ) {
    }
}
