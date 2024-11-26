<?php

namespace App\Presentation\Web\Response\Model\Common;

use OpenApi\Attributes as OA;

readonly class Query
{
    public function __construct(
        public string $connection,
        public string $query,
        public float $execution_time,
        #[OA\Property(type: 'object', additionalProperties: true)]
        public array $params,
        public array|null $trace,
    ) {
    }
}
