<?php

namespace App\Presentation\Web\Response\Model\Common;

readonly class DbInfo
{
    /**
     * @param int $queries_count
     * @param Query[] $queries
     */
    public function __construct(
        public int $queries_count,
        public array $queries,
    ) {
    }
}
