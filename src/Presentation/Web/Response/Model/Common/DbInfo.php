<?php

namespace App\Presentation\Web\Response\Model\Common;

readonly class DbInfo
{
    /**
     * @param int $queries_count
     * @param float $time
     * @param Query[] $queries
     */
    public function __construct(
        public int $queries_count,
        public float $time,
        public array $queries,
    ) {
    }
}
