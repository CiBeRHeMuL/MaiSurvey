<?php

namespace App\Presentation\Web\Response\Model;

use OpenApi\Attributes as OA;

readonly class UserCredentials
{
    public function __construct(
        #[OA\Property(maxLength: 40, minLength: 40)]
        public string $access_token,
        #[OA\Property(maxLength: 40, minLength: 40)]
        public string $refresh_token,
    ) {
    }
}
