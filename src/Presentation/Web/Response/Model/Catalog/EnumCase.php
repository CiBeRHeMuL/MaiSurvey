<?php

namespace App\Presentation\Web\Response\Model\Catalog;

readonly class EnumCase
{
    /**
     * @param string|int $value
     * @param string $name
     */
    public function __construct(
        public string|int $value,
        public string $name,
    ) {
    }
}
