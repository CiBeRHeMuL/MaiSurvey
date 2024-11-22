<?php

namespace App\Presentation\Web\Response\Model\Common;

/**
 * Успешный ответ с пагинацией
 */
readonly class SuccessWithPaginationResponse
{
    public function __construct(
        public PaginatedData $data = new PaginatedData(),
        public Meta $meta = new Meta('dev'),
    ) {
    }
}
